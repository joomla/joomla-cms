#Model-View-Single-Task-Controller (MVSC)
This document is incomplete.
###What is MVSC?
MVSC is a new designed pattern that I am proposing to replace the Joomla! Legacy Model-View-Controller (MVC) architecture.
###MVSC is not MVC
Although MVSC creates clean separation of logic, data and presentation layers, it is not just another flavor of MVC. Below is a list of the characteristics that distinguish MVSC from MVC

#### No M-V-C Triads
The concept of Model-View-Controller Triads does not apply to MVSC. Although all of these elements still exist, they are not bound to a 1-to-1 relationship.

The controllers in MVSC are designed to use a 1-to-N relationship. This a controller can execute it's logic on any class that supports the required interface. 

#### Single Task Controller (STC)
As the name of this pattern suggests, each controller is given complete authority over a single task.
 
In MVSC the term "Task" represents a single unit of logic that when given equivalent inputs produces the same output every time.

The term "Task" should not be confused with single responsibility as defined by the single responsibility principal. 

Tasks are similar to verbs in human language. By that I mean that although the verb "to drink" has a very clear meaning, the actual process of drinking consists of several smaller actions 

```PHP
    $toDrink = array('To grip the glass', 'To raise the glass to your mouth', 'To pour the liquid in your mouth', 'To swallow the liquid');
```

In the same way, a "Task" has a clear meaning, but the processes involved may consist of several smaller actions.

##### Autonomy and Authority

STCs are autonomous and have full authority over their task. Changing the behavior of one STC will not change the behavior of other STCs. 

This means that they are free to evolve and change to accomplish their task without the fear of side effects.

Because a STC has full authority over its task, changing the behavior of the STC will change that behavior throughout the system. 

This means that a defect in the STC will be apparent throughout the system which makes bug identification easier.

This also reduces the search area for other types of bugs. 

If a bug is found in one extension, then it is a problem in the extension. If its is present in all extensions then the defect is connected to the the task controller involved.







