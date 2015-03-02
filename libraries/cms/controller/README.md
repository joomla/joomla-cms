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







