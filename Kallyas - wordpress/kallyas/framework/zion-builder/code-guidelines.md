# Project: Zion Page Builder

```
@Project: Zion PageBuilder
@Author:
@Author URL:
```

## Code Guidelines


#### #! Spaces vs Tabs
Always use Tabs, not spaces.


#### #! File names

> The file name should be lowercase and describe the content of the document. If the file contains a class, then its name should be exactly as the class name.

**Example**
```
# Simple file name
functions.php

# File containing a class, in this case ZnPb
ZnPb.php
```

#### #! Directory names

> Directories should all be lowercase, words separated by dashes.

**Example**
```
# Simple directory name
admin

# Directory with multiple words
template-parts
```

#### #! PHP

**# Interfaces**
* must start with capital I (from Interface)
* must be camelCase

**Example**
```
interface IZionPageBuilder
{
    //....
}
```


**# Classes**
* must be camelCase
* must start with Capital letter
* must contain the plugin/vendor prefix
* the class name must perfectly describe its functionality
* the class should do whatever its name say, nothing else. Avoid "God" classes (these are classes that do a lot of mostly unrelated things)
* class' opening bracket must go on the next line
* class methods' opening bracket must go on the next line
* classes must have comments and a description
* class methods must have comments and a description
* class methods' name must describe their functionality
* class methods' optional parameters must be placed last
* class variables must have a value when declared
* class variables should have a descriptive comment
* class constants must be written with CAPITAL_LETTERS, separated by underscores and have a descriptive comment
* always declare the class method's visibility (public, protected, private)


**Example**
```
/**
 * Class ZionPageBuilder
 *
 * Class description here
 */
class ZionPageBuilder
{
    /**
     * Holds the error code a service might return when called
     * @type int
     */
     const ERROR_CODE = 403;

    /**
     * Holds the loaded Page Builder elements
     * @type array
     */
     public $page_builder_elements = array();

    /**
     * Initialize the class' default functionality
     * @param mixed $arg1 Description goes here
     * @param mixed $arg2 Description goes here
     * @param bool|false $arg3 Description goes here.
     */
    public function initialize( $arg1, $arg2, $arg3 = false )
    {
        // code here...
    }
}


//#! Usage
$znpb = new ZionPageBuilder();
```

* classes should be static unless needed to be instance based. To easily differentiate the two, think of it this way: you only need instance classes if you need more than one instance of that class on a single request. A good example would be the Page Builder elements, which need to be instantiated more than once on a page.

**Example**
```
/**
 * Class ZionPageBuilderLoader
 *
 * Class description here
 */
class ZionPageBuilderLoader
{
    /**
     * Holds the loaded Page Builder elements
     * @type array
     */
     public static $page_builder_elements = array();


    /**
     * Load all Page Builder elements and populate the internal variable
     * @uses $page_builder_elements
     */
    public static function loadElements()
    {
        // code here...
    }
}


//#! Usage
$page_builder_loaded_elements = ZionPageBuilderLoader::loadElements();
```

* Singletons. Setup the class using this design pattern only when you need one instance of that class at any given time. Singletons will always save their state through the page request's life cycle.

**Example**
```
/**
 * Class ZionPageBuilderSingleton
 *
 * Class description here
 */
class ZionPageBuilderSingleton
{
    private $_var = 0;

    // code here...

    public static function getInstance()
    {
       //....
    }

    public function setValue( $newValue )
    {
       $this->_var = $newValue;

       // Allow this method to be chained
       return $this;
    }

    public function getValue()
    {
       return $this->_var;
    }
}


//#! Usage

$v1 = ZionPageBuilderSingleton::getInstance();

echo $v1->getVar(); // will output 0

$v1->setValue( 2 );

echo $v1->getVar(); // will output 2


// Somwehere else in the code (on the same request cycle)

$v2 = ZionPageBuilderSingleton::getInstance();

echo $v2->getVar(); // will output 2

$v1->setValue( 5 );

echo $v2->getVar(); // will output 5

//#! $v1->getVar() will also display the last value used

echo $v1->getVar(); // will also output 5
```


**# Variables**
* must be lowercase
* must start with lowercase letter
* multiple words must be separated by underscores
* the variable name must describe the variable designation perfectly
  * global variables must have descriptive names
  * internal variables don't have to
* private class variables must have a leading underscore
* public and protected class variables and functions must be declared the same way, no leading underscores.
* always declare the class variable's visibility (public, protected, private)

**Example**
```
// Global variable
$page_builder_elements = array();
```


**# Functions**
> All function names should be camelCase
> Global functions must contain the plugin/vendor prefix: zionMyGlobalFunction(){}
> Function names must describe perfectly their designation

