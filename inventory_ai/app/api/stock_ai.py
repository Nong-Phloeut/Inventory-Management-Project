from fastapi import APIRouter
from pydantic import BaseModel
from app.services.stock_ai_service import stock_ai_analysis

router = APIRouter()

class StockAIRequest(BaseModel):
    product_id: int
    current_stock: int
    lead_time_days: int = 7
    coverage_days: int = 30

@router.post("/stock-ai")
def analyze_stock(request: StockAIRequest):
    """
    Purchase-only AI endpoint.
    """
    return stock_ai_analysis(
        product_id=request.product_id,
        current_stock=request.current_stock,
        lead_time_days=request.lead_time_days,
        coverage_days=request.coverage_days
    )
