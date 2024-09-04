# Use the base image you are currently using
FROM circleci/php:7.4-apache-node

# Install Vercel CLI globally with sudo
USER root
RUN npm install -g vercel
USER circleci