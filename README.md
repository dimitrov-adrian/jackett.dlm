# jackett.dlm
Download Station search module for Jackett (https://github.com/Jackett/Jackett)


## Installation

1. Check - WORKING AND ACCESSIBLE JACKETT INSTANCE

1. Download latest jackett.dlm module

2. Install and enable to your Download Station -> Settings -> BT Search -> Add

3. Enable Jackett and edit settings.

4. Use your jackett instance **host as username**, and **api key as password**
> Example: If your Jackett instance is accessible via *http://192.168.0.1:9117/UI/Dashboard*,
> then the username is **192.168.0.1:9117**

5. Click OK


## Compatibility

Tested on Synology DSM 6.1
(I just have no older version to test)


## Building

```bash
git clone https://github.com/dimitrov-adrian/jackett.dlm.git
cd jackett.dlm.git
make
```

## Testing
```bash
make tests ARGS="<hostname> <apikey>"
````
