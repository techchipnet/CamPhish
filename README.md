# CamPhish

Grab camera shots from a target's phone front camera or PC webcam by just sending a link.

![CamPhish](https://techchip.net/wp-content/uploads/2020/04/camphish.jpg)

## What is CamPhish?

CamPhish is a technique to take camera shots from a target's phone front camera or PC webcam. It hosts a fake website on the built-in PHP server and uses Ngrok & CloudFlare Tunnel to generate a link. This link can be forwarded to the target and accessed over the internet. The website asks for camera permission, and if the target allows it, the tool grabs camera shots of the target's device. 

A GPS location capture feature has also been added.

## Features

This tool includes automatic webpage templates to engage the target and obtain more camera pictures:

* Festival Wishing
* Live YouTube TV
* Online Meeting [Beta]
* GPS Location Tracking

A cleanup script has been added to remove all unnecessary files and logs.

## Tested On:

* Kali Linux
* Termux
* MacOS
* Ubuntu
* Parrot Sec OS
* Windows (WSL)

## Requirements and Installation

This tool requires PHP for the webserver and `wget` for downloading dependencies. First, run the following command on your terminal:

```bash
apt-get -y install php wget unzip
```

### Installing (Kali Linux / Termux / Ubuntu):

```bash
git clone https://github.com/techchipnet/CamPhish
cd CamPhish
bash camphish.sh
```

### Clean logs & unnecessary files:

```bash
bash cleanup.sh
```
The captured camera files and saved locations will also be removed.

## Change Log:

**Version 2.0:** Added GPS Location Tracking
* Added: GPS location capturing functionality
* Added: Google Maps integration for captured locations
* Added: Location accuracy reporting
* Added: Improved loading screen with location request

**Version 1.9:** Enhanced architecture detection
* Added: Improved architecture detection for all CPU types
* Added: Better support for Apple Silicon (M1/M2/M3) Macs
* Added: Automatic detection of ARM, ARM64, x86, and x86_64 architectures
* Fixed: Windows compatibility improvements
* Fixed: CloudFlare Tunnel download issues

**Version 1.8:** Added CloudFlare Tunnel and removed Serveo
* Added: CloudFlare Tunnel support for more reliable connections
* Removed: Serveo tunnel (deprecated)
* Fixed: Various code improvements and bug fixes

**Version 1.7:** Fix and add support
* Fixed: Termux failed to get home directory
* Added: Support for Apple silicon (M1/M2/M3 ARM64)
* Added: Support for arm64 like Raspberry Pi

**Version 1.6:** Fix Ngrok direct link generation
**Version 1.5:** Add new online meeting template
**Version 1.4:** Ngrok authtoken update
**Version 1.3:** Fix Ngrok direct link

---

### Important Notice
Unauthorized re-uploading of this project is prohibited.

#### For More Videos subscribe to [TechChip YouTube Channel](http://youtube.com/techchipnet)

*CamPhish is created to help in penetration testing and the author is not responsible for any misuse or illegal purposes.*

*CamPhish is inspired by https://github.com/thelinuxchoice/ - Big thanks to @thelinuxchoice*
