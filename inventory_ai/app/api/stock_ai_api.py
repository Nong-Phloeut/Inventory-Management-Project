from fastapi import APIRouter
from app.services.stock_ai_service import analyze_stock

router = APIRouter()

@router.get("/stock-ai")
def stock_ai(threshold: int = 10):
    """
    Endpoint to get low stock products.
    Returns a list of products below threshold.
    """
    return analyze_stock(threshold)
