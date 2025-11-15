# Stage 1: Build frontend
FROM node:20 AS build-stage

WORKDIR /app

# Copy package files first (caching)
COPY package*.json ./

# Install npm dependencies
RUN npm install

# Copy the rest of the source
COPY . .

# Build the frontend
RUN npm run build

# Stage 2: Production image
FROM nginx:alpine AS production-stage

# Expose default HTTP port
EXPOSE 5174

# Create app directory
RUN mkdir -p /app

# Copy custom nginx configuration
COPY nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Copy built frontend from build-stage
COPY --from=build-stage /app/dist /app
