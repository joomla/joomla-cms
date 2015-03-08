There is really nothing special about these models. 

I just removed a lot of spaghetti, so that the models behave consistently regardless of where you call them from.

Note since the goal here is to build a solid foundation, I have removed all "performance optimizations". 

#Key Points

1. Public methods do not call other public methods.
2. No internal ACL checks
3. getItems has been replaced with getList 
4. List state is only set in the getList method
5. allowAction method is used for all ACL checks, so this is the only place you need to override ACL
