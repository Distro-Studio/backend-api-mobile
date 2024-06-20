import sys
import face_recognition

def compare_faces(file_path_1, file_path_2):
    image_1 = face_recognition.load_image_file(file_path_1)
    image_2 = face_recognition.load_image_file(file_path_2)

    encodings_1 = face_recognition.face_encodings(image_1)
    encodings_2 = face_recognition.face_encodings(image_2)

    if not encodings_1 or not encodings_2:
        return False

    face_encoding_1 = encodings_1[0]
    face_encoding_2 = encodings_2[0]

    results = face_recognition.compare_faces([face_encoding_1], face_encoding_2)
    return results[0]

if __name__ == "__main__":
    file_path_1 = sys.argv[1]
    file_path_2 = sys.argv[2]
    match = compare_faces(file_path_1, file_path_2)
    print("match" if match else "no match")
