# This is a Moodle Plugin
## Collect, rate and sign Training Records
This is a Moodle Plugin. It is made to collect Training records for hairdresser trainees. 
- It creates a new Activity called "Training records" you can use in courses. 
- Training records contain a description, a date and an image attachment (taken at the training)
- Trainees can add Records in their course. The records are only visible to themselves and to the trainers. 
- Trainers can see all records of the trainees in the activity. They can sign them and rate them. They can also export the data in csv and pdf for each trainee. 

Most of the references in the code are named in German. 
The plugin is compatible to the Moodle App but uploading images does not work in the app. 

## I'm looking for Help
Uploading Images from the Moodle App does not work. I need some Help to get this working. 
https://moodle.org/mod/forum/discuss.php?d=423671


## Open ToDos:
- Finish Translation to English
- Get Uploading Images from the Moodle App to work


# Developing 
## Setup Development Environment
- I develop in Visual Studio Code, but you can use other IDEs
- Install Docker to your development computer
- Change the Directory Volume Mounts in the docker-setup/docker-compose.yml File to your local folders
- Start Docker containers with:
  cd moodledev-dockersetup
  docker-compose up 
- For Windows Users: Change pathes in the scripts/ChangeMonitor.ps1 according to your environment. Open PowerShell and start the script in scripts/ChangeMonitor.ps1 (this will move your files to the moodle dev installation on change)
- Go to the web address (localhost:32430) and login with the admin user devadmin:devadmin and install the plugin


## SSH into Container
```
docker exec -it <ID> /bin/sh
cd /bitnami/moodle
```

## Run after code change:
First SSH into Container then run 
```
cd /bitnami/moodle
php admin/cli/purge_caches.php
chown -R daemon:daemon /bitnami/moodle
chown -R daemon:daemon /bitnami/moodledata
```

## Upgrade Plugin
```
php admin/cli/upgrade.php
```

## Uninstall Plugin
```
php admin/cli/uninstall_plugins.php --plugins=mod_hittrainingsnachweis --run
```


## If you have "invalid permission error" 
SSH into Container then run 
```
chown -R daemon:daemon /bitnami/moodle
chown -R daemon:daemon /bitnami/moodledata
```

# Link to Moodle App Development
https://integration.apps.moodledemo.net/