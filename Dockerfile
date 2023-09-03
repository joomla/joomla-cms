# Use an official Node.js runtime as a parent image
FROM node:14

# Set the working directory in the container
WORKDIR /app

# Copy package.json and package-lock.json to the working directory
COPY package*.json ./

# Install application dependencies
RUN npm install

# Copy the rest of your application code to the container
COPY . .

ENV PORT=8080

# Expose a port (if your application listens on a specific port)
EXPOSE 3000

# Define the command to start your application
CMD ["node", "app.js"]
