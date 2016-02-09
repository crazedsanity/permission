# Permissions System #

This is a generic permissions system.  The idea is to programatically allow/deny 
access to anything based on user, group, and "other" permissions.

This system denies access by default: if a request is made for which there is no 
rule, permission is denied.  This is a pretty basic system, lacking formal tie-ins 
to other tables.  This simplicity is by design: avoiding any unnecessary linkage 
to other tables ensures maximum usability with minimal barrier to entry.