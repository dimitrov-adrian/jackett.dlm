# jackett.dlm

Download Station search module for Jackett (https://github.com/Jackett/Jackett)

## Installation

1. Check - WORKING AND ACCESSIBLE JACKETT INSTANCE

1. Download latest [jackett.dlm](https://github.com/dimitrov-adrian/jackett.dlm/releases/download/1.1.0/jackett.dlm) module (DSM7)

1. Install and enable to your Download Station -> Settings -> BT Search -> Add

1. Enable Jackett and edit settings.

1. Use your jackett instance **host as username**, and **api key as password**

   > Example: If your Jackett instance is accessible via *http://192.168.0.1:9117/UI/Dashboard*,
   > then the username is **192.168.0.1:9117**

1. Click OK

## Compatibility

Tested on Synology DSM >= 6.1

## Building

```bash
git clone https://github.com/dimitrov-adrian/jackett.dlm.git
cd jackett.dlm.git
make
```

## Testing

```bash
make tests ARGS="<hostname> <apikey>"
```
