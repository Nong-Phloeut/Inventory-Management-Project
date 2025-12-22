## 2️⃣ Create Virtual Environment

python -m venv venv
source venv/bin/activate   # Mac / Linux
venv\Scripts\activate      # Windows

## 3️⃣ Install Dependencies
pip install -r requirements.txt

## 4️⃣ Configure Environment Variables

Create .env file:

DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=yourpassword
DB_NAME=inventory


5️⃣ Run the API Server
uvicorn app.main:app --reload
uvicorn app.main:app --host 0.0.0.0 --port 8080
