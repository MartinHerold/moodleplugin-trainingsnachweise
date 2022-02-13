### SET FOLDER TO WATCH + FILES TO WATCH + SUBFOLDERS YES/NO
    $watcher = New-Object System.IO.FileSystemWatcher
    $watcher.Path = "D:\Workspaces\VisualStudioCodeProjects\hittrainingsnachweise\moodleplugin"
    $watcher.Filter = "*.*"
    $watcher.IncludeSubdirectories = $true
    $watcher.EnableRaisingEvents = $true  

### DEFINE ACTIONS AFTER AN EVENT IS DETECTED
    $action = { $path = $Event.SourceEventArgs.FullPath
                $changeType = $Event.SourceEventArgs.ChangeType
                $logline = "$(Get-Date), $changeType, $path"
                Copy-Item -Path "D:\Workspaces\VisualStudioCodeProjects\hittrainingsnachweise\moodleplugin\hittrainingsnachweis" -Destination "D:\Workspaces\VisualStudioCodeProjects\hittrainingsnachweise\moodleinstallation\moodle\mod\" -Recurse
                Add-content "D:\Workspaces\VisualStudioCodeProjects\hittrainingsnachweise\scripts\copylog.txt" -value $logline
              }    
### DECIDE WHICH EVENTS SHOULD BE WATCHED 
    Register-ObjectEvent $watcher "Created" -Action $action
    Register-ObjectEvent $watcher "Changed" -Action $action
    Register-ObjectEvent $watcher "Deleted" -Action $action
    Register-ObjectEvent $watcher "Renamed" -Action $action
    while ($true) {sleep 2}
