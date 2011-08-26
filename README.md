Ariadne Component Library 
========================= 

A high quality component library for PHP 5.3 
-------------------------------------------- 

The Ariadne Component Library is a spinoff from the Ariadne Web 
Application Framework and Content Management System 
[ http://www.ariadne-cms.org/ ]

It is designed to make it simpler to do many common web application
tasks in a way that is easy to extend and is standards compliant and
accessible by default. The components are loosely coupled but can be
used together. All components use dependency injection combined with
default factory methods, so the components are very flexible but easy
to use.

It doesn't provide a MVC (Model View Controller) framework or URL 
router, that is left to other frameworks. Instead it provides the 
following components:

XML/HTML parsing, dom methods and generation
--------------------------------------------
SimpleXMl and DOMDocument are powerful tools in PHP, but they are
harder to use than should be necessary. The XML and HTML component
defines an interface similar to SimpleXML but expanded with many
useful DOM methods only found in DOMDocument and DOMNode. In
addition it fixes the problems with namespaces in SimpleXML and 
DOMDocument and when converted to a string keeps the full XML intact.


URL parsing and generation
--------------------------
This component makes it easy to parse a URL into its component parts,
modify it and rebuild it into a string.


W3C style event handling
------------------------
An event publish/subscribe component that mimicks the way W3C DOM
events work. It has a bubble and capture phase and events are
handled in order and can prevent the default action to occur or even
stop the remainder of the event handlers to fire.


HTTP client
-----------
A simple way to do HTTP requests, using PHP's streams. Also provides
an interface to $_GET, $_POST, etc. with automatic tainting:


Automatic Variable Tainting
---------------------------
When using the ar\connect\http::getvar methods, variables can be
automatically tainted. This means that they are encapsulated in an
object which automatically filters the content when converted to a
string. The filter can be changed using PHP's provided filters.

Only strings or arrays of strings are tainted. 


Pluggable Architecture
----------------------
The component library is designed to be easy to extend with your
own components. The static classes containing the factory methods
allow you to add your own factory methods to them, so your
components can be seamlessly integrated.
