# main.py
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

# Import routers
from app.api.stock_ai_api import router as stock_ai_router
from app.api.purchase_recommendation_api import router as purchase_router

# Import database engine and Base (models use this Base)
from app.database.db import engine, Base

# Create all tables (optional: only for dev/testing)
# Base.metadata.create_all(bind=engine)  # Alembic will handle migrations

# Initialize FastAPI app
app = FastAPI(title="Inventory Stock AI")

# Mount routers
app.include_router(stock_ai_router, prefix="/api")
app.include_router(purchase_router, prefix="/api")

# Enable CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # You can restrict this in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Health check endpoint
@app.get("/")
def health():
    return {"status": "AI service running"}
