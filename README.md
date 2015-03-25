#Jiauyo - Educational MVC Framework

## What is it? / Why?

Jiaoyu is a very simple MVC PHP framework.  It is first and foremost an **educational** project I did.  Do not use this code in production.  I did it largely because I'd used several MVC frameworks in a variety of programming languages and often felt that while I understood the overall layout I'd get confused when following traces or trying to read the code.  The entry and exit points weren't always clearly defined and frequently the class inheritance trees were quite dense and involved a lot of indirection to actually find the mechanism for a given action.  I decided to try and write my own, with most of the features I found useful in MVC frameworks and with the explicit goal of the code being easy to follow along with.  To this end I attempted to keep inheritance down to a minimum.  This of course comes at the expense of flexibility but for my purposes it was very much worth it.

Of all of the MVC frameworks I've used my favorite two have by far been Laravel for PHP and Play! for Java and Scala.  I shamelessly admit to borrowing heavily from their outer-facing APIs - especially Laravel's.  If you are familiar with Laravel, you'll notice a lot of similarity almost immediately.  A huge difference is that Laravel uses their Inversion of Control container to make the outward facing API be mostly a collection of static calls to what appear to be static classes.  Under the hood there are instantiated objects performing the work.  In Jiaoyu this is not the case - those static calls really are static calls to static classes.  This means in most cases if you want to see how a given call works you just need to read the code for the method you are calling.  Again, flexibility sacrificed for the sake of being able to very simply follow the action.

Once again, and I can't really state this enough, you **should not use this framework** for any kind of code that even *might* end up in production somewhere.  If you do so and run into problems, you are completely on your own.  Consider this fair warning.  If you want a production ready MVC framework for PHP with a similar (but much more fully featured) API and with good documentation, security patches and all the stuff you actually need for production code, simply use [Laravel](http://laravel.com).

## Requirements ##

These days there are a multitude of ways to install PHP web apps.  For development, I use Apache 2 with modphp, usually on Debian or one of the *buntu derivatives.  I realize mod-php has fallen out of favor and many people use very different setups now.  In any case, for the sake of simplicity the framework is compatible with the builtin php webserver so that's pretty much what I'll describe.  Since you read the warnings above and won't be using the framework in production anyway, there's no reason that the built-in server wouldn't do the job.  So here's a list of the stuff you'll need to have installed:

-  PHP 5.5+ (uses the password_hash() functions - check the FAQ section for potential workaround)
-  PHP **json** library
-  PHP **mcrypt** library
-  PHP **mysql** library (for PDO/mysql support)
-  Mysql server if you plan on using the database layer / Atlas ORM at all (mariadb works fine)

On Debian/Ubuntu you can get all of this with apt-get.

    sudo apt-get install php5 php5-cli php5-mysql php5-json php5-mcrypt mysql-server-5.5

## Installation ##

Once you've got the system requirements, you'll need to clone this repository somewhere onto your system.

    git clone https://github.com/gabriel-comeau/jiaoyu.git

Now, you can work right out of that folder after running the setup script or you can instead use the script to create a new Jiaoyu project elsewhere on your system.  I recommend the second option so that you can use git to version your project separately from the framework proper.  For quick messing around though, the first might be a little (very little) faster.  Here's how to do the second one:

1.  Navigate to inside a folder where you want to create a new jiaoyu project.  Example:  cd /home/gabe/src/myjiaoyuproject
2.  Call the jiaoyu executable script from inside that folder: /path/to/jiaoyu/bin/jiaoyu new

This will create the folder structure and copy the Jiaoyu libraries to your project's folder.  It will also create the CoreConfig file which is always included first (used so that the autoloader and other things know where everything can be found).

The next thing you'll need to do is set up the config file.  Navigate to app/conf and edit *config.json*

If you want to use a mysql database, you'll need to fill in a host, username, password and database name here.  If the "use_auth" flag is set, it will require a valid database config (and the "use_database" flag to be on) to work.

With this done, you can verify that everything has functioned by navigating to the webroot and then starting the PHP built-in webserver:

    cd webroot
    php -S localhost:1337

Then navigate in a browser to http://localhost:1337 and you should see a welcome message with some very basic CSS styling.


## A trip through the framework

When you make the above request and get the welcome page, you are taking a typical path through the framework's code.  Let's have a look at how that works:

- First, there is an .htaccess file which directs all incoming requests to the index.php file in the webroot. This is the main (and only, from a client's point of view) entry point into your application.  This .htaccess is respected by Nginx, Apache2 and the builtin HTTP server.  If you use something like IIS, best of luck to you.

- index.php performs two direct includes:  CoreConfig.php and the Jiaoyu Autoloader.  CoreConfig mostly just contains the absolute directory path to the application's root folder.  The autoloader needs this information to be able to find classes as they get called.

- Next index.php calls AutoLoader::init().  This first does a require_once() on the helpers file - just a collection of global spaced plain PHP functions.  Then it registers one of its methods as the the spl autoloader - PHP's runtime will call its' DoLoad method when it attempts to call a class it doesn't know about yet.  The whole AutoLoader.php class is less than 100 lines with comments, so if you're interested in how to write a very simple class autoloader, here's a possible way of doing it.

- Now that we can load framework classes without having to manually include them, we call JiaoyuCore::init().  This method initializes the other subsystems so that they're ready to process the request.

    - First it registers error handlers so that any uncaught exceptions get sent to a proper error page (there's a debug one which provides lots of information about the stack trace and a non-debug one which just explains that there's been an error).  As well as being the last stand against uncaught exceptions, it can capture many regular php errors (though not fatals sadly).

    - Next it reads the application configuration from that config.json file you filled out earlier.  Malform that file and you'll crash (but the error handler will get it and you'll be told why you crashed)

    - If the configuration says that the database is to be used, it will initialize a connection to it (this same connection will get re-used throughout the request's lifecycle).  Note that it would be more efficient to do this lazily - only initialize the database connection the first time something actually tries to use it.  Perhaps I'll change this in the future.

    - The PHP session is next initialized.  This calls session_start() and also reads any cookies which were attached to the request so that they'll be available through the Session class.

    - If the Auth system is enabled in the configuration it will be initialized.  This will check the above-mentioned cookies for the session cookie and read it if it is present.  If so, it will check session for the user's unique ID and attempt to load the current user object into memory by reading the data from the database via the model layer.  This is why the 'auth_model_class', 'auth_model_user_id_column' and 'auth_model_password_column' fields exist in the config - so that the Auth system can be customized to use any model class the user desires.  From any point after this, you can check if there's a logged-in user and get this user object from the Auth system.

    - Since the application might need to start generating URLs, it reads enough of the incoming request to figure out the correct base url for the application and stores it as a static variable on the JiaoyuCore (since it gets used from all over the place)

    - The routes.json file (mapping of URLs to controller/controller-method pairs) gets parsed and the route objects are built.

    - The widgets.json file (explained further down) also gets parsed and widget objects are prepared.


-  Now that subsystems are ready to go, we return to index.php and see the next call is to build a request object.  Excepting cookies and session data, the HttpCore method that does this reads the various $_SERVER, $_GET and $_POST values and turns them into a simpler-to-user Request object.

-  The request object gets sent to the Router's dispatch() method.  This is how the framework figures out which controller class to instantiate and which method within that class to call.  This is worth investigating as it contains (IMO) some of the most complex code in the framework.

    - A collection of all possible Route objects was created during the subsystem initialization.  Dispatch calls findRouteForRequest in order to find one which matches.  That method iterates through all of the routes in the collection and then performs a series of checks for a matching one.  It returns the first match it finds or nothing if nothing matched.  The matching process checks the following:

        - If the request uses an HTTP method not supported by this route (the request is a GET and the route only supports POST) it is eliminated.
        - Next is an exact match check.  If the defined route in the routes file is "/hello" and there request is for "/hello" this is a definite match.
        - Because routes can have dynamic params as part of their urls: "/blog/posts/some-string-key-for-post" the rest of this method is dedicated to looking for these types of matches.  If a route is defined with a single parameter:  "/blog/posts/{postKey}" then the above example will match.  However if the request was for "/blog/posts/some-string-key/something-else" that would **not** be a match.

    -  If, after checking all of the possible routes in the collection nothing matched then the framework returns a 404 response.
    -  If it did find a match, it instantiates a controller, the type of which is defined in the route.  This controller class is then autoloaded and it should be a child of the framework's Controller class.  If overriding the default constructor, make sure to take in the Request object as a constructor argument and to call parent::__construct($request).
    -  If this route has URL parameters: "/blog/posts/{postKey}" it will figure out which value from the url becomes which argument in the call to the controller method.  This is done by the order the params come in by.
    - Finally it calls the method of the instantiated controller.  If there were url params, they'll be passed as arguments to the method call.  So our example blog post call would have a method like: *"public showPost($postKey)"* in its controller which would get called with that postKey value being set to whatever value was on the end of the URL.

-  Dispatch having been complete, control of the request is passed to your controller's method.  From here you can do whatever you need to do in your controller - check auth status, perform HTTP redirects look at any incoming values (from the URL params, from the GET params or from the POST params), make calls to your model layer and finally create a response.

-  In the case of our "welcome" call the controller is only a few lines long:

    $homeView = View::make('homeview');
    $rendered = $this->layout->embed('content', $homeView, true);
    return Response::html($rendered);

-  This creates a new view object whose template will be called "homeview.php" and found in the app/views folder.  This view doesn't take any variables in.
-  Next, the view is embedded into the layout.  MainController has a protected member called layoutName which is the template file for the basic layout.  This template has a variable called content in it.  The homeview will get processed and turned into straight html content, which then gets set as the value of the layout's $content variable.  This process is repeated again for the layout template - it gets rendered and turned into html, stored as a string in that controller's $rendered variable. None of this causes a lick of data to be sent to the client - all of this disables the output buffer so that you have full control over writing the response only when you are ready.

-  Finally, the rendered text is sent to the client via Response::html().  This sets a status code of 200 and sends the rendered content to the client as type text/html.  There are also Response::json() and Response::text() which will set the right MIME types when transmitting to the client.

This sums up a typical request/response cycle through the framework.  It's still quite a bit of stuff happening, but if you follow this list and look at the code (start in index.php) and then trace through each call manually, you will hopefully find it understandable (assuming some PHP experience and some basic web experience of course).


## How to use things

While I don't intend on writing full documentation for a framework I don't want people using for real code, I will explain some of the features with examples so that you can experiment with things to then go and look under the hood to see how they're implemented.

### CLI Tool

The jiaoyu command line tool can perform a couple of tasks I found myself doing by hand pretty frequently.  It expects to be run out of the framework's bin folder (the checkout of the framework, not the project).  This system is admittedly not even close to as clever as Composer and their vendor directories.  Alas, this was a lot simpler.  There are two main ways to use the tool:

#### With Path:

The first is to add it to your path.  Assume your git checkout is located at **~/local/jiaoyu**.  You need to edit your shell's config file (on Linux with bash, this is usually **~/.bashrc** and on OSX you can find it in **~/.bash_profile**)

    PATH="~/local/jiaoyu/bin":$PATH

Obviously, adjust the folders to match whatever your checkout is.  You'll probably want to rescan your shell config file:

    source ~/.bashrc

Now that the jiaoyu CLI script is in your path, from anywhere you like you can access it by just typing "jiaoyu".  To run the commands this way, you can navigate to your Jiaoyu project's folder and run them:

    cd path/to/my/project
    jiaoyu check-configs

#### Without Path:

If you don't want to run the script out of your path, you can also run it the other way.  Again, assume that your git checkout of the jiaoyu framework is ~/local/jiaoyu:

    ~/local/jiaoyu/bin/jiaoyu <command> <target>

*command* is the cli command you want to run
*target* is the path to the jiaoyu project you want to run that command on

In either case, the command and target parameters can be used (even if it is in your path, you can still call jiaoyu to run a command on a different folder than the one you are currently in)

#### Commands

Here are the commands available:

-  **new** <target> - Creates a new project at <target> (or in current directory if target not specified)
-  **update** <target> - Updates the framework files in the project located at <target> (or current dir)
-  **reconfig** <target> - Re-creates the CoreConfig.php file project at <target> (or current dir)
-  **check-config** <target> - Checks the .json configuration files for validity in project at <target> (or current dir)

The idea of the update command is that you go to your git checkout of jiaoyu and do "git pull" to update it.  Then you have an actual Jiaoyu project somewhere and you want to update the framework files for it - run "jiaoyu update /project/path" and it'll copy over the new framework files to it, while leaving your files untouched.

### Routes

All application routes are defined in the app/conf/routes.json file.  This is parsed at the beginning of a request and then the request is matched against these until one is found or the list is exhausted (Do you want a 404?  Because that's how you get a 404).

Here's an example of the "/" route - it's very simple, supporting only the http GET method and taking no parameters:

    {
        "name": "home",
        "path": "/",
        "controller": "MainController",
        "action": "homeAction",
        "methods": [
            "GET"
        ]
    }

The route "name" field is used for generating URLs to routes from the application.  Say you've got a route named "logout" whose url is something "/user/logout".  You want to generate a logout link in your template:

    <a href="/user/logout">Logout</a>

That's fine for simple URLs but for longer ones, why do all that when you can instead do:

    <a href="<?php echo route('logout'); ?>">Logout</a>

While definitely longer in this short-url case, this means you can change the url path for logout in the routes file whenever you like - as long as it has the same name it will keep on working.  More importantly, that route() helper function can generate URLs with URL params too.  Without the helper:

    <a href="/posts/<?php echo $nextPost; ?>">Next Post</a>

With:

    <a href="<?php echo route('show_post', array($nextPost)); ?>">Next Post</a>

If your route has multiple URL parameters, make sure to pass them in the correct order to route() by putting them into that array argument in the right order.

The "path" field of the route definition is the actual URL that it will match.  This includes both the hard-defined part "/foo/bar" but also the URL params.  Url params are indicated with {} curly braces:  "/foo/{bar}".  The name is for you - since PHP isn't strongly typed, the arguments are passed into the controller method in the order they appear in the array, but naming things properly can help you remember what they mean when you come back to them after some time has passed.

The "controller" and "action" fields are the name of the controller class you want this route to instantiate and then the name of the method you want to call on it, respectively.

Finally the "methods" array is a list of all acceptable HTTP methods for this route.  You can have more than one if you want to, for example, have a route accept both "POST" and "PUT".


### Controllers

Controllers are where you put the *glue* code for your application in a typical MVC application.  They call the model layer and set up the views.  Since the incoming request is sent to the controller, they're also contextually the most logical place to handle things like session data or pass incoming form data off for validation.

In Jiaoyu, you typically store your controllers in app/controllers.  They should extend the framework's base Controller class.  This is because the router will call a constructor with this signature:

    public function __construct(Request $request)

This means that all of your controller actions can access the current request with $this->request.

You *can* create a controller which doesn't extend the frameworks - you just need to have a constructor with a matching signature.  Ultimately the router code that does the calling is:

    $controllerName = $route->controller;
    $controller = new $controllerName($request);

    if ($route->hasUrlParams()) {
      $actionParams = self::getUrlParams($request, $route);
      call_user_func_array(array($controller, $route->action), $actionParams);
    } else {
      call_user_func(array($controller, $route->action));
    }

Action methods don't require any specific naming scheme or anything - they should be be public and they should take the same number of arguments as their calling route has url parameters.  So if you've got a route like this:

    {
        "name": "showPost",
        "path": "/blog/show/{postKey}",
        "controller": "BlogController",
        "action": "showAction",
        "methods": [
            "GET"
        ]
    }

You'd want to define a controller like so:

    # app/controllers/BlogController.php
    <?php
      class BlogController extends Controller {

        public function showAction($postKey) {
          ...
        }
      }


So let's look at the request object which is a member variable of your instantiated controller.  What can you do with it?

The Request class is very simple and easy to read.  It's got a few public member variables which are setup by the HttpCore class when it process the incoming request (before dispatch).  It has a single method:

    /**
     * Gets $_GET and $_POST input from the request by key or null if it isn't there.
     *
     * @param String $key The key to search for
     * @param String $type Search the 'get', 'post' and 'file' params or all of them.  Defaults to all.
     * @return Mixed The value if found or null if not.
     */
    public function get($key, $type = 'all')

This is just a convenience method for getting input.  You put in the key you want and specify if you're looking for a specific type of input (post, get or file).  If there's a value for that key, you get it back.  If not, you get a null.  If you don't specify an input type it will search all of them.  The order it does so is "GET", "POST" and then finally "FILE".

Controllers are the place you'll typically use the Response class, so let's look at that.  Response is the class responsible for actually sending content back to the requesting client.  It has some static helper methods:

    Response::html($payload, $code = 200)
    Response::text($payload, $code = 200)
    Response::json($payload, $code = 200)

Those three send whatever's in the payload with "text/html", "text/plain" or "application/json" MIME types as appropriate.  The default response code is 200, but you can set a different one if you like.

It also has:

    Response::redirect($url, $code = 302)

Which is used to generate redirects.  If you prefer to use a 301, you can change the code.  This method will set the location header and not output anything to the client except those headers.  To generate URLs, you can use the route() helper function described in the routing section above.

Finally, because this comes up so much, there's a predefined method for 404's:

    Response:fourOhFour(Request $request)

This one takes in the entire request object because it does a couple of extra things.  The application will check the global configuration (app/conf/config.json) for a key called "custom_404_page".  If you set this value to the name of a template, the 404 method will render that template and pass it the request object (available to the template as the variable $request).  If you haven't set this value it will use a builtin framework 404 page which basically uses the request to show the URL the user it attempting to reach.

**All** of these methods end up following the exact same procedure, if you check the code out.  They all internally create a response object, setting it up with the right status code, payload (if needed), headers and a flag indicating whether or not the payload should be sent (so things like redirects, which are headers-only, done also send anything as the response body).  After this is created it, they pass it to:

    Response::send(Response $resp)

Which does the actual work of closing off the session (since the request is now over), sending any new cookies which need to be sent, sending headers and finally (if there is one and the response object has the flag set) echos the payload to PHP's output buffer (which is what gets sent back to the client).

To do custom responses, you are free to instantiate your own response objects and then use send() to send them.

### Views

While custom templating languages appear to be de rigueur in MVC frameworks these days, I didn't feel inclined to write one largely because I never use them.  PHP is of course a templating language at its heart so I continue to use it as such.  By manipulating the output buffer appropriately, embeddable templates are simple enough to generate so that's what I've used.

An important thing to keep in mind when working with views is that there is a distinction between a View object and a template file.  A template file is a plain PHP file - mixed in with all of the HTML you like and it can do things like "echo" directly.  A view is an object, of the type View.

You make a view object like so:

    $myView = View::make('someTemplate', array('someVarForTpl' => $something));

The view class will look for a file called "someTemplate.php" in the app/views folder.  You can use sub-folders to organize your views - "admin/someTemplate" will look for "someTemplate.php" in app/views/admin.

The array passed in as the second argument to make() represents the variables which will be available to the template when it is rendered.  So if your template looks like:

    <ul class="post-list">
        <?php foreach ($posts as $post): ?>
            <li class="post-list-item"><a href="<?php echo route('showPost', array('postKey' => $post->key)); ?>"><?php echo $post->title; ?></a></li>
        <?php endforeach; ?>
    </ul>

When you'd call View::make, you'd want to make sure that the template has a list of posts called $posts:

    $posts = Post::all();
    $postListView = View::make('postListView', array('posts' => $posts));

Making the view doesn't immediately render it.  This is because views are nestable (as arbitrarily deep as you care to do).  To turn a view into a (usually) HTML string, you'll want to call it's render() method.  Render returns the processed template file - if it was a typical PHP/HTML mix, all of the PHP will be executed, with the variables bound to the values passed in as the array in the call to make.  Unlike when you normally execute PHP code with things like echo calls it is not passed immediately to the browser.  This is the output buffer manipulation I mentioned earlier.  Here's the code for render():

    public function render() {
      // First we need to get all of the variables out of the array and as "normal" variables.
      if ($this->templateVars) {
        foreach ($this->templateVars as $varKey => $varVal) {
          ${$varKey} = $varVal;
        }
      }

      // Using the output buffer we can include templates which will execute regular
      // php code but not actually display them to output.
      ob_start();
      include($this->templateFile);
      $content = ob_get_clean();
      return $content;
    }

Using the (at least to me) somewhat unusual ${$varKey} syntax, we can create variably-named-variables.  Next ob_start() prevents the output from being sent to the client.  A standard php include() causes the template file to immediately be executed in the scope of the render() function (those variable-variables also exist in the same scope, which is why they become available to the template).  Finally, the output buffer is flushed into the $content variable (as opposed to the client) via ob_get_clean().  It's get_clean because it first retrieves the content and then clears the buffer.

The content is returned to the view's caller (typically the controller) and then when you want to actually send it to the client, you use the Response class discussed in the controller section to do so.

This covers the basic use of views.  The other thing they can do worth mentioning is the embedding/nesting I brought up before.  For my part, I typically use a basic layout and then embed a more specific view to it.  This can be done manually, or via the embed() method.  Here's a more thorough example, using the manual method for clarity, of both the view and controller level:

    #parentView.php
    <html>
      <head>
        <title>Some Web App</title>
      </head>
      <body>
        <h1>Some web app, wow!</h1>
        <div class="content">
          <?php echo $content; ?>
        </div>
      </body>
    </html>

    #childView.php
    <div class="someChildContent">
      The current unixtime is: <strong><?php echo $time; ?></strong>
    </div>

Now the controller method:

    #timeController.php
    <?php

      class TimeController extends Controller {

        public function showTime() {

          $time = time();
          $myChildView = View::make('childView', array('time' => $time));
          $myParentView = View::make('parentView', array('content' => $myChildView->render()));

          Response::html($myParentView->render());
          // done
        }
      }
    ?>

The final result, sent to the browser will look like:

    <html>
      <head>
        <title>Some Web App</title>
      </head>
      <body>
        <h1>Some web app, wow!</h1>
        <div class="content">
          <div class="someChildContent">
            The current unixtime is: <strong>1426878453</strong>
          </div>
        </div>
      </body>
    </html>


Now, with the embed() version, you could alter the controller to look like this:

    #TimeController.php
    <?php

      class TimeController extends Controller {

        public function showTime() {

          $time = time();
          $myChildView = View::make('childView', array('time' => $time));
          $myParentView = View::make('parentView', array());

          Response::html($myParentView->embed('content', $myChildView, true));
          // done
        }
      }
    ?>

The last "true" passed to embed tells it to immediately render the parent view.  The child, though passed as a view object, is always instantly rendered by embed, because it can't be bound as a variable to the parent until this has been done.  If you left the true off (it default to false so you don't always need to put it in), embed() wouldn't return anything.

What's the point of using embed() instead of the first method?  In this case - very, very little.  Embed exists because of *when* variables can be passed into templates.  Normally, you must have all of the template's data set up and ready to pass in when you call View::make().  Embed gives you another chance to add a variable (a view to be rendered) to an already instantiated view object.  Why bother?  It's useful when combined with a property of Controllers. I shamelessly took this idea from Laravel, because I used the idiom so frequently when doing Laravel apps:

Controllers can have a view as a member variable.  This view, or "layout" which is how I usually use it, doesn't get any variables passed into it when it is created.  It exists for the purpose taking 1 or more other views later on.

The homepage action walked through at the top uses the idiom.  It's controller looks like this:

    #MainController.php
    class MainController extends Controller {

      // Name of the layout template
      protected $layoutName = "layout";

      /**
       * Show the homepage
       */
      public function homeAction() {
        $homeView = View::make('homeview');
        $rendered = $this->layout->embed('content', $homeView, true);
        return Response::html($rendered);
      }
    }

When the router instantiates a copy of mainController to call, the Controller constructor will call initLayout().  If the $layoutName property isn't empty, initLayout() calls View::make() on that layout name, passing it an empty array (no template variables yet).  At the time this happens, there won't be anything else to send (since the controller action hasn't been run yet).  Hence, embed is used to pass in the child template to the variable named $content in the parent (the layout).


### Widgets

Widgets are something in between a Controller and a View.  They are largely independent of the regular request->dispatch->controller->action->response app lifecycle.  They exist for reasons of code reuse.  Imagine you've got a blog (the most original example on all the interwebs!).  On your blog you want to show a sidebar with a basic listing of the five most recent posts on it.  You want to show this on the homepage of your blog, you want to show it on the "post" view of your blog - you want to show it nearly everywhere.  You don't want to put it in your layout though - unless you violate the MVC concept and start making model-layer calls directly from the layout's template file (don't do this).  Since the layout was created before a controller action was called (see explanation above, in the views section on embedding) there's nowhere to put those model-layer calls to generate that posts-list.  What to do?

One way to do it is to make a separate view for that list.  That view gets embedded into your page view, which in turn gets embedded into the layout.  Imagine:

    <?php
    class BlogController extends Controller {

      public function showPost($postKey) {
        $post = Post::findByKey($key);
        if ($post == null) {
          Response::fourOhFour($this->request);
          return; // don't execute any more code
        }

        $postListView = View::make('postList', array('posts' => Post::getForList()));
        $postShowView = View::make('postView', array('post' => $post));
        $postShowView->embed('postList', $postListView, false);

        Response::html($this->layout->embed('content', $postShowView, true));
      }
    }

This does the job, but you'd have to repeat quite a bit of it in order to do it again somewhere else.

This is the problem widgets are designed to solve.  They work a little differently than normal view-controller pairings, so let's go ahead and define that posts list as a widget.

First, you need to define the widget in the config file:

    #app/conf/widgets.json
    [
      {
          "name": "posts_list",
          "template": "postList",
          "controller": "PostListWidgetController"
      }
    ]

When Jiaoyu's initialization process runs at the beginning of a request, it reads the widgets file and registers all of the widgets so they can later be called.  It's stored this way to be more
"globally" accessible and outside of the regular controller->response cycle.  It doesn't actually execute any widget controller actions or render anything unless you call one so don't worry about performance if you're making database calls in widgets and don't want them called for nothing.

Each widget has a template (which is a regular php template, like any other views).  The template goes in app/views like any other.  This template will be passed variables once it runs, like any other, so you can go ahead and echo stuff out as desired.  Here's the example template for our posts list:

    #app/views/postList.php
    <div class="sidebar">
      <ul class="post-list">
          <?php foreach ($posts as $post): ?>
              <li class="post-list-item"><a href="<?php echo route('show_post', array('postKey' => $post->key)); ?>"><?php echo $post->title; ?></a></li>
          <?php endforeach; ?>
      </ul>
    </div>

So we know that to render is requires a $posts variable - an array of Post objects.

OK, so now we define that PostListWidgetController class.  It goes into app/controllers since that what it is.  However, it does NOT extend Controller.  It extends WidgetController instead.  WidgetController is an abstract class with an abstract method execute() that must be defined by any child classes.  It also comes with a concrete constructor built in.  The execute() method that we have to define should return an array.  This array works like the View::make array (the second argument) - the "keys" in become variables in the template which hold the values.

So here's ours (with a very-pretend model layer):

    #app/controllers/PostListWidgetController
    <?php
    class PostListWidgetController extends WidgetController {

      public function execute() {
        $posts = Post:: Post::getForList();
        return array('posts' => $posts);
      }
    }

Now how do we call it?  A single line of code!  Somewhere in our layout.php template is a good place for this particular widget, since it's so simple:

    #app/views/layout.php
    <html>
      <head>
        <title>Blag!</title>
      </head>
      <body>
        <div class="content">
          <?php echo $content; ?>
        </div>
        <?php echo Widget::run('posts_list'); ?>
      </body>
    </html>

That's it.  It'll get rendered and echo'd out.  Now this is obviously a very simple widget that requires absolutely no external information to perform its duties.  Sometimes that won't be
the case.  Let's have a look at the entirety of that abstract WidgetController class:

    #lib/jiaoyu/controller/WidgetController.php
    <?php

    /**
     * A controller extending the widget controller class will be instantiated
     * and executed when calling Widget::run().  The name of this controller class is
     * defined in the widget config file but in order for it to work properly it must
     * extend this abstract class.
     *
     * @author Gabriel Comeau
     * @package Jiaoyu
     */
    abstract class WidgetController {

      /**
       * Constructor method - takes in an array of parameters and makes
       * all of the values in it available as properties of the object,
       * with the names of the props being the keys.
       *
       * @param Array $params Associative array of the objects properties.
       */
      public function __construct(Array $params) {
        foreach ($params as $k => $v) {
          $this->{$k} = $v;
        }
      }

      /**
       * This is what actually happens when the controller is "called" during widget
       * generation.  This should return an associative array which will be
       * passed on as parameters to the receiving template.
       *
       * @return Array
       */
      abstract public function execute();
    }

Way more comment than code - pretty easy.  Look at that __construct() function though.  Not abstract. It takes in an array of parameters and via the variable-variable technique, makes each key from the array a member variable of the instantiated controller object (holding the value from the array).  If you go back to the above example though, you'll see we never directly instantiated a widget object.  Here's the Widget class' run() method:

    public static function run($widgetName, $controllerVars = array()) {
      // First we have to find the widget by name from the config file
      $widget = JiaoyuCore::widget($widgetName);
      if (!$widget) {
        throw new ViewException("No widget registered with name $widgetName in widgets.conf");
      }

      // Now we have to instantiate the controller class
      $className = $widget->controllerName;
      $controller = new $className($controllerVars);
      $viewParams = $controller->execute();

      $template = View::make($widget->templateName, $viewParams);
      return $template->render();
    }

Notice the optional argument $controllerVars (defaults to empty array if not set).  If you want your WidgetController implementation to have member variables when you instantiate it, pass them in to the Widget::run() method!  Then in your implemented execute() method, you can use them.

This covers the widget system - its inspiration came from Symfony 1.4's components.

### Sessions and Cookies

This is a shorter section because these are largely just very thin wrappers around $_SESSION and $_COOKIE.  All of the session/cookies stuff happens via the Session class.  Here's the api for sessions:

- Session::get($key)
- Session::set($key, $value)
- Session::delete($key)

For cookies (which can last longer than a single session):

- Session::getCookie($name)
- Session::addCookie($name, $value, $expiry = ONE_DAY_FROM_NOW, $path = "/", $domain = null)
- Session::deleteCookie($name)

addCookie works like set - you can change the value of an existing cookie by writing over it (calling it again).  Cookies can't really be "deleted" with a header (session variables can because they're stored on the server, not the client).  When you call deleteCookie what you are actually doing is setting a new cookie with an expiry in the past.  Seems hacky, because it is.

The final thing to be concerned with are "flash" sessions.  These are a special type of session variable which only persist to the next request.  Most frameworks I've used have them - they're handy for stuff like error messages or "You've successfully been logged out" messages.  One thing to beware of - AJAX requests can and WILL eat the flash sessions - the framework currently does have an isAjax flag on the requests but it is not used to check before cycling through the flash sessions (because you might WANT to use them in ajax calls too).

The API for them is:

Session::setFlash($key, $value)
Session::getFlash($key)
Session::getAllFlash()

Note that calling the get or getAll methods will not return any flash values added during this request.  You only get flash session values from the previous request.  They work pretty well with redirects - here's an example controller:

    #app/controllers/AuthController
    <?php
    class AuthController extends Controller {

      public function logout() {
        if (Auth::getUser()) {
          Auth::logout();
          Session::setFlash('flash_message_ok', "You have been logged out!");
        } else {
          Session::setFlash('flash_message_error', 'You weren't logged in to begin with!');
        }


        Response::redirect(route('home'));
      }
    }

Now in the home view template (or perhaps the layout, if you want to do it more globally):

    <?php if(Session::getFlash('flash_message_ok')): ?>
      <div class="alert ok-alert fade-in">
        <?php echo Session::getFlash('flash_message_ok'); ?>
      </div>
    <?php endif; ?>

    <?php if(Session::getFlash('flash_message_error')): ?>
      <div class="alert error-alert fade-in">
        <?php echo Session::getFlash('flash_message_error'); ?>
      </div>
    <?php endif; ?>

Redirects get sent back to the client's browser, which then in turn makes a new request to the url it was redirected to (in our case, whatever url matches the home route).  Since this is a new request, the now-set flash session value is available and it'll display in this template.


### Auth

The auth module is a simple thing but it's been handy for my use cases.  While it doesn't really belong in a "minimal" MVC framework, I got tired of re-implementing it and just added it in.  It
uses the Model code, the Session code and some of PHP's crypto wrappers to provide login/logout against a database table with a bcrypt hashed password.  It can be enabled / disabled in the main config file at app/conf/config.json with the key "use_auth".  If you are using auth the module you need to set up a bit more config for it in that config file:

    "use_auth": true,
    "auth_model_class": "User",
    "auth_model_user_id_column": "username",
    "auth_model_password_column": "password"

The module will look for your model class "User".  Auth integrates with the Atlas ORM (the model layer) so your auth_model_class should extend Atlas like any other of the ORM classes.  The user_id_column
and password_column are to tell Auth which columns (properties of the ORM objects) to use.  That user_id_column isn't referring to the literal numeric ID in the database.  I admit, this was a poor naming choice.  It's referring to the column that corresponds to whatever the users use to login - username, email - whatever.  It should be a unique column - username's aren't great if two people can have the same one.  Auth will error out if it finds multiple entities for the same "user_id_column" column when it checks credentials.

So you can have a very simple SQL table like so:

    DROP TABLE IF EXISTS `Users`;
    CREATE TABLE `Users` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

Mapped to a model object (just bear with me - explanation of Atlas is coming up soon) like this:

    #app/models/User.php
    <?php
    class User extends Atlas {
      protected static $table = "Users";
    }

That's it.  The auth model would now function (with a really lame user model object).  Here's how you might use it - this is a pretend controller that does logins and logout, using Auth:

    #app/controllers/AuthController
    <?php

    class AuthController extends Controller {

      // Name of the layout template
      protected $layoutName = "layout";

      /**
       * Show the user the login page
       */
      public function showLoginPageAction() {
        $rendered = $this->layout->embed('content', View::make('login', array()), true);
        return Response::html($rendered);
      }

      /**
       * Handle login from POST values
       */
      public function doLoginAction() {
        $user = $this->request->get('username', 'post');
        $pass = $this->request->get('password', 'post');

        if (!$user || !$pass) {
          Session::setFlash('flash_message_error', 'Username and password are required!');
          return Response::redirect(route('login'));
        }

        if (Auth::login($user, $pass)) {
          Session::setFlash('flash_message_ok', 'Logged in!');
          return Response::redirect(route('home'));
        } else {
          Session::setFlash('flash_message_error', 'Incorrect username or password!');
          return Response::redirect(route('login'));
        }
      }

      /**
       * Do logout.
       */
      public function doLogoutAction() {
        if (Auth::getUser()) {
          Auth::logout();
          Session::setFlash('flash_message_ok', 'Logged out!');
          return Response::redirect(route('home'));
        } else {
          Session::setFlash('flash_message_error', 'Already logged out!');
          return Response::redirect(route('home'));
        }
      }
    }


The external facing API of auth is:

- Auth::login($user, $pass)
- Auth::logout()
- Auth::getUser

getUser() returns the currently logged in user, if there is one (null if not).

Under the hood, passwords are expected to be stored as hashed bcrypt (using the new-ish php password_hash() functions).  I wrote a very, very thin wrapper to those functions (like a 1 line wrapper), because previously I had planned on supporting older versions of PHP and would have IRCMaxell's password_hash() backwards compat library.  Since you shouldn't be using this framework anyway, you definitely shouldn't be caring about old php version support.

When you want to add a new user / change a user's password, you'll need to call these wrappers (or password_hash() directly).  Here's a pretend controller method for users to change their password:

    public function changePasswordAction() {
      if (!Auth::getUser()) {
        Session::setFlash('flash_message_error', 'ERROR');
        return Response::redirect(route('home'));
      }

      $newPassword = $this->request->get('new_password', 'post');
      $confirm = $this->request->get('confirm_new_password', 'post');

      if ($newPassword !== $confirm) {
        Session::setFlash('flash_message_error', 'Password and password confirmation must match');
        return Response::redirect(route('change_password_form'));
      }

      // probably other validation to make sure passwords are not really insecure

      $user = Auth::getUser();
      $user->password = CryptoUtils::hash($newPassword);
      $user->save();

      Session::setFlash('flash_message_ok', 'Password updated');
      return Response::redirect(route('home'));
    }


### Atlas ORM

If auth was a little much to be adding to a minimalist framework, an ORM implementation is *really* overdoing it.  Still, this project was all about my education and so I wrote one.  It is very
simplistic - it doesn't actually even support relations so I supposed it's more of an Object-Database-Mapper.

Atlas works by inspecting the table structure of the mapped database table and dynamically building objects against that structure.  It supports the basic CRUD operations but there are no joins or other relational behaviors.  There is also no way to generate a database table structure from a PHP class.  It only goes the other way around.

Here's an example SQL table:

    CREATE TABLE `BlogPosts` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `content` text COLLATE utf8_unicode_ci NOT NULL,
      `posted_date` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

Here's the Atlas model class - it goes into app/models:

    #app/models/Post.php
    <?php

    class Post extends Atlas {

      protected static $table = "BlogPosts";

    }

That's all thats needed for the most basic CRUD ops.  With the classes that extend Atlas the rule of thumb is that instance methods affect a row of data and static methods affect the table.  Some simple examples:

    // create a new post, assign data to it's fields and then save it
    $post = new Post();
    $post->subject = "Atlas ORM considered harmful";
    $post->content = "whole lotta text goes in here";
    $post->posted_date = date("Y-m-d H:i:s");
    $post->save();

    // Now that it's been inserted, it's got an ID
    $postId = $post->id;

    // Subsequent save calls will be updates rather than inserts
    $post->subject = "Atlas ORM isn't even a real ORM";
    $post->save;

    // delete it
    $post->delete();

Note that the id field defined in the database is not optional.  If you want Atlas to track it, you've got to have an autoincrement id field.

Querying is done at the table level, so the methods are static:

    // get a single object, by id:
    //(SELECT * FROM BlogPosts WHERE id = ? LIMIT 1)
    $post = Post::one($someId);

    // get every post in the db
    // (SELECT * FROM BlogPosts)
    $posts = Post::all()->execute();

    // proper querying:
    //(SELECT * FROM BlogPosts WHERE subject = 'foo')
    $posts = Post::where('subject', '=', 'foo')->execute();

    // Chaining methods:
    //(SELECT * FROM BlogPosts ORDER BY posted_date DESC)
    $posts = Post::all()->orderBy('posted_date', 'desc')->execute();


Imagine our post table had an extra column and this constraint:


    CREATE TABLE `BlogPosts` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `content` text COLLATE utf8_unicode_ci NOT NULL,
      `posted_date` DATETIME NOT NULL,
      `user_id` int(10) unsigned NOT NULL,
      PRIMARY KEY (`id`),
      KEY `blog_posts_user_id_foreign` (`user_id`),
      CONSTRAINT `blog_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci


    // Get all posts from a certain user
    $userId = Auth::getUser()->id;
    $myPosts = Post::where('user_id', '=', $userId)->orderBy('posted_date', 'desc')->execute();

Look it's almost like we're relational!  The SET NULL behavior on delete makes it relatively safe to delete a user.  You could also CASCADE instead, if it makes sense for your model.  Keep in mind however, since Atlas doesn't track relations between object types, if you call delete() on a user and the behavior is CASCADE, it will delete all of that user's posts but no delete methods would be called (imagine there were uploaded photos to erase from the server's hard drive).   This means if you want to override Atlas' default delete() method for a model object, make sure any foreign keys defined in the SQL are set to ON DELETE RESTRICT (which at least in mysql, is the default).  This will cause an error if you try to delete a User while they still have BlogPosts in the system.

The thing to keep in mind when using Atlas is that the table methods, with the exceptions of one() and execute() do not return results, they return queries:

    $query = Post::where('subject', 'like', '%foo');

$query here is just a query object.  It doesn't actually execute until you explicitly call execute on it.  This means you can append more clauses:

    $query = $query->andWhere('user_id', '=', $userId);
    $query = $query->orderBy('posted_date', 'desc');
    $results = $query->execute();

The one() method automatically executes because it's just a shorthand for selecting a single object by id.  Here are the methods you can use when querying:

- **where(field, test, value)**   Appends a where clause to a query.  This is identical to andWhere()
- **andWhere(field, test, value)** Same as where().  Only reason there are both is for clarity
- **orWhere(field, test, value)** Appends an or-where clause to a query.
    - **Field** is the name of the column you want to test
    - **Test** is the operand:  =, !=, >, <, <=, >=, like etc
    - **Value** is the value to test against.
-  **orderBy(field, direction)** Orders the results.  Field is the column, direction is "asc" or "desc"
-  **limit(lim)** Adds a limit "lim" to the number of results
-  **offset(off)** Offset results, starting at number "off"

That covers the basic usage of Atlas - if you're interested in one possible (and very simplistic) ActiveRecord design, you can check out the lib/Atlas folder (everything starts with Atlas.php, which is the class meant to be extended by model objects).  There is a lot of use of PHP's dynamic typing used in generating the model objects on the fly.

I know I'm starting to sound like a broken record, but if you are looking for ActiveRecord for PHP, there are much, much better implementations of it out there than Atlas.  Use one of them!

## FAQ

-  Should I used this for any reason that isn't educational?
    - **No**

-  I want to use this on a PHP version older than 5.5.
    - The big reason 5.5 is required is for the password_hash() functions.  IRCMaxell's written a compatibility library which if you were to include, should fix your problems. I'd look into the autoloader to see how the Helpers.php functions/const file is included and then include IRCMaxell's library there.

-  Pull requests?
    -  Short answer?  Probably not.  Long answer?  Maybe.  Straight up bug fixes - sure thing.  New features?  Probably not - the point is simplicity and to be able to trace through framework code easily, in order to learn how these sorts of things work.  If you do a new feature and it lines up to this perfectly I might take it, but no promises.

-  Why not just use Laravel/Symfony/Cake/Whatever ?
    -  Definitely use those.  100%.  Those frameworks are well tested, well supported, well documented and proven for production use.  I didn't write this framework to really use it for anything serious - I wrote it to test my own understanding of MVC framework architecture.  Is this reinventing the wheel?  Absolutely!  That remains the only real way to learn about wheels.

-  Composer support?
    - While I use it in life to get packages, I'm embarrassingly unfamiliar with how to setup packages to use it.  I didn't bother because really, you *shouldn't be using this framework for anything serious anyway*.

-  License?
    - Check LICENSE file - MIT license.  Feel free to take any code you like and do whatever you like with it, just don't pretend you're me or expect me to help you if something doesn't work.  I'm also not liable for any damage (financial or otherwise) you do by using this code - you did ignore my repeated warnings not to after all!
