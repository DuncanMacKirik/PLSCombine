# PLSCombine
A quick-and-dirty PHP script for combining multiple IPTV playlists into one.  
Can be used from command line (shell) or on a web server ~~(eg, to provide a URL for a single playlist)~~ (to be called either manually or by a scheduler), to unify and/or filter one or several other playlists according to specified rules.  
Said rules are configured by editing combine.ini.  
Each line specifies a single playlist source using this format:  
```  
<url>=<type>[:<options>]  
```  
<url> can be a local path, or a URL (http(s)://, ftp://, or any other protocol supported by PHP file functions; see https://www.php.net/manual/en/wrappers.php for the full list).  
<type> can be one of these:  
  * ```full``` - include all channels from this source;  
  * ```all-grp:\<groupname\>``` - include all channels from this source and place them into separate group "\<groupname\>";  
  * ```nodups``` - include all channels from this source, but only if their names don't already exist.  
  
If ```nodups``` option is not enabled for a source, then channels imported from it with names that already exist will be renamed like this (assuming channel named DupChannel already exists):  
```  
DupChannel (1)  
DupChannel (2)  
...
```  
etc.  
