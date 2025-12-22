from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api import stock_ai_api, purchase_recommendation_api  # import from app.api

app = FastAPI(title="Inventory Stock AI")

# Mount routers under /api
app.include_router(stock_ai_api.router, prefix="/api")
app.include_router(purchase_recommendation_api.router, prefix="/api")

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Health check
@app.get("/")
def health():
    return {"status": "AI service running"}
