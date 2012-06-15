## The Facebook Package

### Using the Facebook Package

The Facebook package is designed to be a straightforward interface for working with Facebook. It is based on the Graph API. You can find documentation on the API at [http://developers.facebook.com/docs/reference/api/](http://developers.facebook.com/docs/reference/api/).

#### Instantiating JFacebook

Instantiating JFacebook is easy:

```php
$facebook = new JFacebook;
```

This creates a basic JFacebook object that can be used to access publicly available resources on facebook.com which don't require an active access token.

Sometimes it is necessary to provide an active access token with the required permissions. This can be done by instantiating JFacebookOAuth.

Create a Facebook application at [https://developers.facebook.com/apps](https://developers.facebook.com/apps) in order to request permissions.
Instantiate JFacebookOAuth, passing the JRegistry options needed. The API key, API secret and callback URL (which is the script's path) from the Facebook application are passed through the JRegistry object. By default you have to send headers manually in your application, but if you want this to be done automatically you can set JRegistry's option 'sendheaders' to true.

```php
$options = new JRegistry;
$options->set('consumer_key', $consumer_key);
$options->set('consumer_secret', $consumer_secret);
$options->set('callback', $callback_url);
$options->set('sendheaders', true);
$options->set('authmethod', 'get');
$oauth = new JFacebookOAuth($options);

$facebook = new JFacebook($oauth);
```
Now you can authenticate and request the user to authorise your application in order to get an access token, but if you already have an access token stored you can set it to the JFacebookOAuth object and if it's still valid your application will use it.

```php
// Set the stored access token.
$oauth->setToken($token);

$access_token = $oauth->authenticate();
```

When calling the authenticate() method, your stored access token will be used only if it's valid, a new one will be created if you don't have an access token or if the stored one is not valid. The method will return a valid access token that's going to be used.

Set scope to the JFacebookOauth object. Scope is a comma separated list of requested permissions:

```php
$oauth->setScope('read_stream,publish_stream');
```
          
#### Accessing the JFacebook API's objects

The Facebook package has 12 objects of the Graph API currently implemented:
* Album
* Checkin
* Comment
* Event
* Group
* Link
* Note
* Photo
* Post
* Status
* User
* Video

Once a JFacebook object has been created, it is simple to use it to access Facebook:

```php
$user = $facebook->user->getFeed($user_id);
```
   
This will retrieve an array of Post objects containing (up to) the last 25 posts.

#### More Information

The following resources contain more information
* [Joomla! API Reference](http://api.joomla.org)
* [Facebook Graph API Reference](http://developers.facebook.com/docs/reference/api/)
* [Web Application using JFacebook package.](https://gist.github.com/edaee9488fe77da6692e)
