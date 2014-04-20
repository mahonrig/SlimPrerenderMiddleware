SlimPrerenderMiddleware
=======================

Simple middleware for Slim PHP Framework, for prerendering of JS applications
 using prerender.io. Uses a string of known bots to forward prerender requests.
This is setup for apps using pushState history urls, not #! fragments.

Usage
=======

Include PrerenderMiddleware in your index.php, then:

    $app = new \Slim\Slim();

    /* Initiate our prerender middleware */
    $app->add(new \PrerenderMiddleware('http://service.prerender.io/', 'YOUR TOKEN HERE'));

For every request it checks and matches against a string of known bots,
if any match, the request is sent to the backend url using cURL, which returns
the prerendered body. 
