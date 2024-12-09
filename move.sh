#!/bin/bash

# Tentukan direktori asal dan direktori tujuan
source_dir=~/CamPhish
target_dir=/sdcard/Download/

# Cari semua file .png di direktori sumber dan pindahkan
for file in "$source_dir"/*.png; do
    if [ -f "$file" ]; then
        mv "$file" "$target_dir"
        echo "Moved: $file"
    fi
done

echo "Pindah file by Wanz Xploit selesai!"
