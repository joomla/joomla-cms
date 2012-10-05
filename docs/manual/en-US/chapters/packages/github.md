## The Github Package

### Using the Github Package

The Github package is designed to be a straightforward interface for
working with Github. It is based on version 3 of the Github API. You can
find documentation on the API at
[http://developer.github.com/v3/.](http://developer.github.com/v3/)

JGithub is built upon the JHttp package which provides an easy way to
consume URLs and web services in a transport independent way. JHttp
currently supports streams, sockets and CURL. It is possible to create a
custom context and inject it into the JGithub class if one so desires.

#### Instantiating JGithub

Instantiating JGithub is easy:

```php
$github = new JGithub;
```

This creates a basic JGithub object that can be used to access
publically available resources on github.com.

Sometimes it is necessary to specify additional options. This can be
done by injecting in a JRegistry object with your preferred options:

```php
$options = new JRegistry();
$options->set('api.username', 'github_username');
$options->set('api.password', 'github_password');

$github = new JGithub($options);
```

#### Accessing the JGithub APIs

The Github package is still incomplete, but there are four object APIs
that have currently been implemented:Gists, Issues, References, Pull
Requests

Once a JGithub object has been created, it is simple to use it to access
Github:

```php
$pullRequests = $github->pulls->getList('joomla', 'joomla-platform');
```

This will retrieve a list of all open pull requests in the specified
repository.

#### A More Complete Example

See below for an example demonstrating more of the JGithub package:

```php
$options = new JRegistry();
$options->set('api.username', 'github_username');
$options->set('api.password', 'github_password');
$options->set('api.url', 'http://myhostedgithub.example.com');

$github = new JGithub($options);

// get a list of all the user's issues
$issues = $github->issues->getList();

$issueSummary = array();

foreach ($issues as $issue)
{
	$issueSummary[] = '+ ' . $issue->title;
}

$summary = implode("\n", $issueSummary);

$github->gists->create(array('issue_summary.txt' => $summary));
```

#### More Information

The following resources contain more information:  [Joomla! API
Reference](http://api.joomla.org), [Github API
Reference](http://developer.github.com).
