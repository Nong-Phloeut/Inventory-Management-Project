from fastapi import APIRouter
from app.services.purchase_recommendation_service import recommend_purchase

router = APIRouter()

@router.get("/purchase-recommendation")
def purchase_recommendation():
    """
    Endpoint to get AI suggested products for purchase form.
    """
    return recommend_purchase()
