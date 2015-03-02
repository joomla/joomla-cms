#Model-View-Single-Task-Controller (MVSC)

This document is incomplete.

###What is MVSC?

MVSC is a new designed pattern that I am proposing to replace the Joomla! Legacy Model-View-Controller (MVC) architecture.
###MVSC is not MVC

Although MVSC creates clean separation of logic, data and presentation layers, it is not just another flavor of MVC. 

Below is a list of the characteristics that distinguish MVSC from MVC.

#### No M-V-C Triads
The concept of Model-View-Controller Triads does not apply to MVSC. Although all of these elements still exist, they are not bound to a 1-to-1 relationship.

The controllers in MVSC are designed to use a 1-to-N relationship. 

This means a controller can execute it's logic on any class that supports the required interface. 

#### Single Task Controller (STC)
As the name of this pattern suggests, each controller is given complete authority over a single task.
 
In MVSC the term "Task" represents a single unit of logic that when given equivalent inputs produces the same output every time.

The term "Task" should not be confused with single responsibility as defined by the single responsibility principal. 

##### Tasks are verbs

Tasks are the application verbs. In human language a verb has a very clear meaning, but the actual process of doing said verb may consist of several smaller actions. 

In the same way, a "Task" has a clear meaning within the application, but the processes involved may consist of several smaller actions.

In this implementation I have used that similarity in the naming of the controller classes. 

With the exception of abstract base classes and the ajax controller, STCs use verbs as names.

##### Autonomy and Authority

STCs are autonomous and have full authority over their task. Changing the behavior of one STC will not change the behavior of other STCs. 

This means that they are free to evolve to accomplish their task without the fear of side effects.

Because a STC has full authority over its task, changing the behavior of the STC will change that behavior throughout the system. 

This means that a defect in the STC will be apparent throughout the system which makes bug identification easier.

This also reduces the search area for other types of bugs. 

If a bug is found in one extension, then it is a problem in the extension. If its is present in all extensions then the defect is connected to the the task controller involved.

## How MVSC works in Joomla!

One of the things that separates from other attempts at replacing the Legacy MVC is that from the beginning the goal was to make it work in Joomla now. 

This meant that any implementation that would required alteration of the core functionality wasn't even considered.

Because of this MVSC is 100% compatible with Joomla 3.x, the only change made to core classes was the addition of 3 new functions and 1 property to the JApplicationCms class. 

However even these changes are not required for MVSC to work.

# What has changed?

## $task Variable

The most notable of which is the redefinition of the $task variable.

### In Legacy MVC

In Legacy MVC extensions the $task variable had two possible formats. 

#### String Value

The first was a plain string value consisting of just the controller method.

```PHP
$task = 'save';
```

The above configuration would result in execution of the save method located in the ViewController associated with the value of the $view variable.

That is to say if $view = 'articles', the MyComponentControllerArticles::save method would be executed.

#### Dot Delimited String Value

The second format of the task variable was a dot delimited string consisting of the controller name and the method  or "viewController.method"

```PHP
$task = 'articles.save';
```

The above task string would also result in execution of MyComponentControllerArticles::save method

### In MVSC

In MVSC extensions the $task variable respects the two formats, string and dot delimited string, but since STC only have one public facing method (besides setters and getters)

Both the string format and the dot delimited string format represent controller names or "taskController.taskController"

Each taskController is executed from left to right thereby giving you the ability to chain tasks together to create more robust interactions. 

#### String Value

```PHP
$task = 'store';
```

if the class exists, the above task string will result in execution of MyComponentControllerStore::execute or fallback to the JControllerStore::execute method, if it does not.

#### Dot Delimited String Value

```PHP
$task = 'store.close';
```

This task string will result in execution of the store controller as before, then immediately execute either MyComponentControllerClose::execute or JControllerClose::execute methods

## The $view variable

### In Legacy MVC

The Legacy MVC truly is a view centric system. The $view variable was used to determine which controller, model and view execute, manipulate or display. 

If you executed the 'publish' task from the 'articles' view, then the MyComponentControllerArticles::publish method executed the MyComponentModelArticles::publish method, and redirected to the MyComponentViewArticles view.

### In MVSC

In comparison, MVSC is a resource based system. The unbinding of Model-View-Controller triads has decentralised the system and distributed responsibility evenly to all parts of the system.

In order to do that I had to add a new variable to represent the model. So in MVSC the model is represented by the $resource variable. 

Although the value of this approach is difficult to communicate effectively, I'll try to use some examples to illustrate this point more clearly.

#### Example

In MVSC it is easy to use different models to supply data to a view without increasing complexity of the extension. 

In one of my extensions I had the requirement to display a very specific data structure that had two separate sources (one from the local database, the other from an api call).

In Legacy MVC, I had a few options

1. I could create a separate MVC triad to display the API data.
2. I could override the controller and push a different model in to the view depending on some variable.
3. I could override the controller and push both models into the view and then choose which to pull data from depending on some variables
4. I could add the API call logic to my model, override my getItems method and use some variable to when to pull api data vs local data. 

Although there are probably more, I think that's enough to illustrate this point. 

The problem with all of these options is that they require me to increase the complexity of the extension disproportionally to achieve this simple requirement.

Because MVSC isn't bound to the triad system, achieving this requirement is easier. All I need to do is create a model to retrieve the API data and use the $view and $resource variables in my route.

index.php?option=mycomponent&view=view&resource=model1
index.php?option=mycomponent&view=view&resource=model2

Both will route to the same view, the only difference is that the display controller will push a different model into the view.

Complexity of the extension only increases by 1 additional model. 

## Component Routing

IMO the Legacy MVC component routing system is effectively broken. I have come to this option, because in legacy MVC there are multiple routes to everything. 

### In Legacy MVC

    1.index.php?option=mycomponent&view=view
    2.index.php?option=mycomponent&view=view&task=display

This is a very simple example. Both these routes will result in displaying the view, but they will take two very different paths through the code to get there. 

    1. will execute the MycomponentController::display method.
    2. will execute the MyComponentControllerView::display method.

So if you have special access or conditional logic, then you have to either duplicate that logic or inherit the logic to insure that no matter what route is taken the logic is executed.

You also have to keep track (at least subconsciously) of which route you are using. 

This additional cognitive strain increases the likelihood of human error, which results in buggy behavior.

### In MVSC

In MVSC there is only one route to any give task within the context of a component. Every execution cycle starts in the MycomponentController and is then routed to the appropriate STC. 

Since there is only one STC responsible for any given task, the same route is taken every time without exception. This alone reduces extension developments cognitive strain by 10X.




