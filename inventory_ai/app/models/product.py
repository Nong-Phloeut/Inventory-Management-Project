from sqlalchemy import Column, Integer, String, ForeignKey, Float, DateTime
from sqlalchemy.sql import func
from app.database.db import Base
from sqlalchemy.orm import relationship

class Product(Base):
    __tablename__ = "products"

    id = Column(Integer, primary_key=True)
    name = Column(String)
    code = Column(String, unique=True)
    category_id = Column(Integer, ForeignKey("categories.id"))
    unit_id = Column(Integer)

    price = Column(Float)
    cost = Column(Float)
    purchase_items = relationship("PurchaseItem", backref="product")
    supplier_id = Column(Integer, ForeignKey("suppliers.id"))

    supplier = relationship("Supplier")


    created_at = Column(DateTime(timezone=True), server_default=func.now())
