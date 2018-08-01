## Magnus.Router

Router implementations for MagnusCore based on Dispatch protocol

    © 2017 MagnusCore and contributors.

    https://github.com/MagnusCore/MagnusRouter



Introduction
------------

Routing is the process of taking some starting point and a path, then resolving the object that path refers to as a handler. This process is common to almost every web application framework (transforming URLs into controllers), RPC system, and even filesystem shell. Other terms for this process include: "traversal", "dispatch", or "lookup".

Object router is simply a flavor of the routing process that attempts to resolve path elements as a chain of object attributes. This is contrary to the typical routing process involving the use of regex matching in PHP web frameworks. The main cost of this regex matching is the O(n) worst-case performance, in some cases the router continues to seek for more specific routes resulting in every single route being evaluated at least once. This can get particularly nasty in the case of issuing a 404. Certain router implementations will attempt to coerce this process in something resembling a tree for performance gains at great cost of readability. With Object routing, the best AND worst case scenario is O(depth). If a 404 is to be issued, it can terminate on the first object evaluated.

This router is based on a [dispatch protocol](https://github.com/marrow/Webcore/wiki/Dispatch-Protocol) and is not intended for direct use but rather as part of a framework. This does not mean that router cannot be used by itself.

Installation
------------

In order to avoid excessive dependencies, simply download this repository and place it in your vendor directory and ensure your autoloader can instantiate the ObjectRouter.php file.

Usage
-----

Review the examples, this should give you the basic run down of how an entire application is composed using this router. For best results, use with Nginx as the processing speeds are already so fast that Apache alone is doubling the request latency.

With Nginx, add this to your server block
`try_files $uri $uri/ /index.php?$args;`

With Apache, add this to the server or .htaccess file
`RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]`
