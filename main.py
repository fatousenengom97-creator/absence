import io
import os
from datetime import datetime
from fastapi import FastAPI, UploadFile, File, HTTPException, Depends
from sqlalchemy import create_engine, Column, String, Integer, DateTime, LargeBinary
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session
import cv2
import numpy as np

# 1. Configuration Base de données MySQL
DATABASE_URL = "mysql+pymysql://root:@localhost/gestionabsence"
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

class Student(Base):
    __tablename__ = "etudiants"
    
    # Alignement précis avec la structure de ta table MySQL existante
    id = Column(Integer, primary_key=True, index=True) # Clé primaire auto-incrémentée de Laravel
    user_id = Column(Integer)
    codePar = Column(String(255))
    dateNaissance = Column(DateTime, nullable=True)
    lieuNaissance = Column(String(255), nullable=True)
    idClasse = Column(Integer)
    face_photo = Column(LargeBinary) # La colonne LONGBLOB ajoutée manuellement

class Attendance(Base):
    __tablename__ = "presences"
    id_presence = Column(DateTime, default=datetime.now, primary_key=True)
    student_id = Column(String(50))
    status = Column(String(20), default="Present")

app = FastAPI(title="API Gestion des Absences - UFR SATIC (OpenCV Light)")

# Déplacer la création des tables au démarrage pour éviter les boucles infinies de rechargement
@app.on_event("startup")
def startup_event():
    Base.metadata.create_all(bind=engine)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# Charger le détecteur de visage par défaut d'OpenCV
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

def extract_face(image_bytes):
    """Détecte un visage, le recadre et le redimensionne en 100x100 pixels gris"""
    nparr = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if img is None:
        return None
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, 1.1, 3)
    
    if len(faces) == 0:
        return None
        
    # Prendre le premier visage détecté
    (x, y, w, h) = faces[0]
    face_roi = gray[y:y+h, x:x+w]
    # Redimensionner pour avoir exactement la même taille pour la comparaison
    face_resized = cv2.resize(face_roi, (100, 100))
    return face_resized.tobytes()

@app.post("/register")
async def register_student(code_par: str, user_id: int, id_classe: int, file: UploadFile = File(...), db: Session = Depends(get_db)):
    # On vérifie si l'étudiant existe déjà via son codePar (matricule)
    db_student = db.query(Student).filter(Student.codePar == code_par).first()
    if db_student and db_student.face_photo is not None:
        raise HTTPException(status_code=400, detail="Cet étudiant possède déjà une photo de référence.")

    try:
        file_bytes = await file.read()
        face_data = extract_face(file_bytes)
        
        if face_data is None:
            raise HTTPException(status_code=400, detail="Aucun visage détecté sur la photo.")
            
        if db_student:
            # Si l'étudiant existe déjà (créé par Laravel), on met à jour son visage
            db_student.face_photo = face_data
            message = f"Le visage de l'étudiant au code {code_par} a été ajouté avec succès."
        else:
            # Sinon, on crée un nouvel enregistrement complet
            new_student = Student(codePar=code_par, user_id=user_id, idClasse=id_classe, face_photo=face_data)
            db.add(new_student)
            message = f"L'étudiant avec le code {code_par} a été créé et enregistré avec succès."
            
        db.commit()
        return {"status": "success", "message": message}
        
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail=f"Erreur d'enregistrement : {str(e)}")

@app.post("/scan")
async def scan_attendance(file: UploadFile = File(...), db: Session = Depends(get_db)):
    # Récupérer uniquement les étudiants qui ont un visage enregistré
    students = db.query(Student).filter(Student.face_photo.isnot(None)).all()
    if not students:
        raise HTTPException(status_code=400, detail="Aucun visage de référence n'est enregistré dans la base de données.")

    try:
        file_bytes = await file.read()
        current_face = extract_face(file_bytes)
        
        if current_face is None:
            return {"status": "unknown", "message": "Aucun visage détecté sur le scan."}
            
        current_array = np.frombuffer(current_face, dtype=np.uint8)

        best_match_code = None
        min_diff = 15.0 # Seuil d'erreur de ressemblance (plus bas = plus strict)

        for s in students:
            known_array = np.frombuffer(s.face_photo, dtype=np.uint8)
            
            # Calcul de la différence globale absolue entre les deux visages
            err = np.sum((current_array.astype("float") - known_array.astype("float")) ** 2)
            err /= float(current_array.shape[0])
            err = err / 100 # Normalisation simple
            
            if err < min_diff:
                min_diff = err
                best_match_code = s.codePar

        if best_match_code:
            attendance_record = Attendance(student_id=best_match_code, status="Present")
            db.add(attendance_record)
            db.commit()
            
            return {
                "status": "recognized",
                "student_code": best_match_code,
                "message": f"Présence validée pour le code étudiant {best_match_code} !"
            }
        
        return {"status": "unknown", "message": "Étudiant non reconnu."}
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail=f"Erreur d'analyse : {str(e)}")