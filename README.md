# PLSCombine
A quick-and-dirty PHP script for combining multiple IPTV playlists into one.  
Can be used from command line (shell) or on a web server (eg, to provide a URL for a single playlist), to unify and/or filter one or several other playlists according to specified rules.  
Said rules are configured by editing combine.ini.  
Each line specifies a single playlist source using this format:  
```  
<url>=<type>[:<options]  
```
