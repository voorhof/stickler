#!/bin/bash
set -e  # Exit immediately if any command fails

# Define the target directory
BIN_DIR="$HOME/bin"

# Define the path to the locally bundled FFmpeg archive
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FFMPEG_ARCHIVE="$SCRIPT_DIR/ffmpeg-release-arm64-static.tar.xz"

# Ensure the bin directory exists
mkdir -p "$BIN_DIR"

# Make sure the bundled archive exists
if [ ! -f "$FFMPEG_ARCHIVE" ]; then
    echo "FFmpeg archive not found at $FFMPEG_ARCHIVE" >&2
    exit 1
fi

# Change to the bin directory
(
    cd "$BIN_DIR"

    # Copy the locally bundled FFmpeg archive
    echo "Copying local FFmpeg archive..."
    cp "$FFMPEG_ARCHIVE" ffmpeg.tar.xz

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
