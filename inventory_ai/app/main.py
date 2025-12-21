from fastapi import FastAPI
from app.api import stock_ai

app = FastAPI(title="Inventory Stock AI")

app.include_router(stock_ai.router, prefix="/api")

@app.get("/")
def health():
    return {"status": "AI service running"}
