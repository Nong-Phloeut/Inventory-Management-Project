ENV = "prod"  # change to "prod" for deployment

CONFIG = {
    "dev": {
        "DB_HOST": "127.0.0.1",
        "DB_PORT": 3306,
        "DB_USER": "root",
        "DB_PASSWORD": "",
        "DB_NAME": "inventory_management_db"
    },
    "prod": {
        "DB_HOST": "db",
        "DB_PORT": 3306,
        "DB_USER": "root",
        "DB_PASSWORD": "root",
        "DB_NAME": "inventory_management_db"
    }
}

class Settings:
    def __init__(self):
        config = CONFIG[ENV]
        self.DB_HOST = config["DB_HOST"]
        self.DB_PORT = config["DB_PORT"]
        self.DB_USER = config["DB_USER"]
        self.DB_PASSWORD = config["DB_PASSWORD"]
        self.DB_NAME = config["DB_NAME"]

settings = Settings()
