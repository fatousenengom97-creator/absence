import cv2
from insightface.app import FaceAnalysis

app = FaceAnalysis()
app.prepare(ctx_id=0)

img = cv2.imread('test.jpg')

faces = app.get(img)

print("Nombre de visages détectés :", len(faces))