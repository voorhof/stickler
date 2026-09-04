#!/bin/bash
set -e  # Exit immediately if any command fails

# Define the target directory
BIN_DIR="$HOME/bin"

# Ensure the bin directory exists
mkdir -p "$BIN_DIR"

# Change to the bin directory
(
    cd "$BIN_DIR"

    # Download FFmpeg
    echo "Downloading FFmpeg..."
    curl --silent --show-error --fail -L -o ffmpeg.tar.xz "https://johnvansickle.com/ffmpeg/releases/ffmpeg-release-arm64-static.tar.xz"

    # Extract the archive
    echo "Extracting FFmpeg..."
    tar -xf ffmpeg.tar.xz

    # Rename the extracted directory
    mv ffmpeg-*-static ffmpeg

    # Set execute permissions
    chmod +x ffmpeg/ffmpeg

    # Clean up
    rm -f ffmpeg.tar.xz
)

echo "FFmpeg installation completed successfully!"
