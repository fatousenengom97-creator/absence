import base64
import json
import re
import cv2
import numpy as np
import mysql.connector
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

app = FastAPI(title="API de Reconnaissance Faciale - UFR SATIC (OpenCV)")

# Autoriser les requêtes venant de l'application Laravel (port 8000)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configuration de la connexion à la base de données Laravel
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'gestionabsence'
}

# Chargement du détecteur de visage OpenCV (Haar Cascade)
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

known_face_vectors = []
known_face_ids = []


def charger_biometrie_bd():
    """Charge tous les vecteurs faciaux enregistrés en base de données."""
    global known_face_vectors, known_face_ids
    known_face_vectors.clear()
    known_face_ids.clear()
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT etudiant_id, faceVector FROM donnees_biomettriques WHERE faceVector IS NOT NULL")
        lignes = cursor.fetchall()
        for ligne in lignes:
            vecteur_liste = json.loads(ligne['faceVector'])
            vecteur_np = np.array(vecteur_liste, dtype=np.uint8)
            known_face_vectors.append(vecteur_np)
            known_face_ids.append(ligne['etudiant_id'])
        print(f"[{len(known_face_ids)}] visages d'étudiants chargés avec succès depuis la BD.")
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Erreur lors du chargement de la base de données : {e}")


@app.on_event("startup")
async def startup_event():
    charger_biometrie_bd()


class ImagePayload(BaseModel):
    image: str  # Chaîne base64 envoyée par Laravel


def decoder_image(image_base64):
    image_str = re.sub('^data:image/.+;base64,', '', image_base64)
    image_bytes = base64.b64decode(image_str)
    np_arr = np.frombuffer(image_bytes, np.uint8)
    return cv2.imdecode(np_arr, cv2.IMREAD_COLOR)


def extraire_visage(img):
    """Détecte un visage, le recadre et le redimensionne en 100x100 pixels gris."""
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, 1.1, 5)

    if len(faces) == 0:
        return None

    (x, y, w, h) = faces[0]
    face_roi = gray[y:y+h, x:x+w]
    face_resized = cv2.resize(face_roi, (100, 100))
    return face_resized


@app.post("/api/extraire")
async def extraire_vecteur(payload: ImagePayload):
    """Extrait le vecteur facial d'une photo, pour l'enregistrement d'un étudiant."""
    try:
        img = decoder_image(payload.image)
        if img is None:
            return {"success": False, "message": "Format d'image invalide"}

        visage = extraire_visage(img)
        if visage is None:
            return {"success": False, "message": "Aucun visage détecté sur la photo. Réessayez."}

        vecteur_liste = visage.flatten().tolist()
        return {
            "success": True,
            "face_vector": json.dumps(vecteur_liste)
        }
    except Exception as e:
        return {"success": False, "message": f"Erreur Python : {str(e)}"}


@app.post("/api/identifier")
async def identifier_visage(payload: ImagePayload):
    """Compare une photo captée en direct avec tous les visages connus, pour le pointage."""
    global known_face_vectors, known_face_ids
    try:
        img = decoder_image(payload.image)
        if img is None:
            return {"status": "error", "message": "Format d'image invalide"}

        visage = extraire_visage(img)
        if visage is None:
            return {"status": "no_match", "message": "Aucun visage détecté sur la caméra"}

        if not known_face_vectors:
            return {"status": "inconnu", "message": "Aucun étudiant enregistré en base de données"}

        visage_actuel = visage.flatten().astype("float")

        meilleur_score = float('inf')
        meilleur_id = None

        for idx, vecteur_connu in enumerate(known_face_vectors):
            err = np.sum((visage_actuel - vecteur_connu.astype("float")) ** 2)
            err /= float(visage_actuel.shape[0])

            if err < meilleur_score:
                meilleur_score = err
                meilleur_id = known_face_ids[idx]

        seuil = 2000.0  # À ajuster selon les tests réels (plus bas = plus strict)

        if meilleur_score <= seuil:
            confiance = max(0.0, 1.0 - (meilleur_score / seuil))
            return {
                "status": "success",
                "etudiant_id": int(meilleur_id),
                "confiance": confiance
            }

        return {"status": "inconnu", "message": "Visage non reconnu dans le système"}

    except Exception as e:
        return {"status": "error", "message": f"Erreur interne du serveur Python : {str(e)}"}


@app.post("/api/recharger")
async def recharger():
    """Force le rechargement de la mémoire des visages connus."""
    charger_biometrie_bd()
    return {"success": True, "message": "Mémoire biométrique synchronisée avec la base de données."}