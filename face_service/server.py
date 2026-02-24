from flask import Flask, request, jsonify
from flask_cors import CORS
import face_recognition

app = Flask(__name__)
CORS(app)

# Email of the user whose face is stored in known_faces/user.jpg
KNOWN_USER_EMAIL = "khalilboujemaa@gmail.com"


@app.route("/verify", methods=["POST"])
def verify():
    try:
        file = request.files["image"]

        # Load known face
        known_image = face_recognition.load_image_file("known_faces/user.jpg")
        unknown_image = face_recognition.load_image_file(file)

        known_encodings = face_recognition.face_encodings(known_image)
        unknown_encodings = face_recognition.face_encodings(unknown_image)

        if not known_encodings or not unknown_encodings:
            return jsonify({"match": False, "error": "No face detected"})

        known_encoding = known_encodings[0]
        unknown_encoding = unknown_encodings[0]

        results = face_recognition.compare_faces([known_encoding], unknown_encoding)
        distance = face_recognition.face_distance([known_encoding], unknown_encoding)

        matched = bool(results[0])

        response = {
            "match": matched,
            "confidence": float(1 - distance[0]),
        }

        # Include the email so Symfony knows which user to authenticate
        if matched:
            response["email"] = KNOWN_USER_EMAIL

        return jsonify(response)

    except Exception as e:
        return jsonify({"error": str(e)})


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
