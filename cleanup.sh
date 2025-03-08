#!/bin/bash
# Cleanup script for CamPhish
# Removes all unnecessary files and logs

echo "Starting cleanup of unnecessary files and logs..."

# Remove log files
echo "Removing log files..."
rm -f *.log
rm -f .cloudflared.log

# Remove temporary location files
echo "Removing temporary location files..."
rm -f location_*.txt
rm -f current_location.bak

# Remove captured images
echo "Removing captured images..."
rm -f cam*.png

# Remove temporary HTML files
echo "Removing temporary HTML files..."
rm -f index.php
rm -f index2.html
rm -f index3.html

# Clean saved locations directory but keep the directory itself
echo "Cleaning saved locations directory..."
if [ -d "saved_locations" ]; then
    rm -f saved_locations/*
fi

# Remove any other temporary files
echo "Removing other temporary files..."
rm -f LocationLog.log
rm -f LocationError.log
rm -f Log.log

echo "Cleanup completed successfully!" 