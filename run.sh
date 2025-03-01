#!/bin/bash

sudo apt install python3 python3-pip curl websockify git
mkdir flutter-panel && cd flutter-panel
git pull https://github.com/TheBombGamer/flutter-panel.git
pip install -r requirements.txt

