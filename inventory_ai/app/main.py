from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.stock_ai_api import router as stock_ai_router
from app.api.purchase_recommendation_api import router as purchase_router
from database import Base, engine

app = FastAPI(title="Inventory Stock AI")
# import models
from app.models.user import User
from app.models.employee import Employee
from app.models.category import Category
from app.models.product import Product
from app.models.stock import Stock
from app.models.purchase import Purchase
from app.models.purchase_item import PurchaseItem
from app.models.sale import Sale
from app.models.notification import Notification
from app.models.supplier import Supplier

# Mount routers under /api
app.include_router(stock_ai_router)
app.include_router(purchase_router)

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
