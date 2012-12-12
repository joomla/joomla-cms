## The Image Package

This package comprises of 2 main classes, `JImage` and `JImageFilter` which has 8 filter sub-classes that it can use to apply a desired filter to your image. `JImage` depends on the `GD` php extension to be loaded on your server. More information on `GD` can be found at: http://php.net/manual/en/book.image.php

Manipulating images in raw PHP using the `GD` image* functions requires a lot of boilerplate code. The intent of this package is to handle those requirements and make it simple for developers to accomplish those tasks through easy to use (and remember) methods.

All classes in this package are supported by the auto-loader so can be invoked at any time.

### JImage

#### Construction

When creating a new `JImage` object, the constructor will check that the `gd` extension is loaded, and throw a `RuntimeException` if it is not.

The constructor takes a single optional `$source` parameter. This argument can be one of two things:

- A variable containing an existing, valid image resource created using a `imagecreate*` method.
- A string containing a valid, absolute path to an image

If you choose the first option, the class sets the protected property `$handle` to the provided image resource.

If you choose the second option, the class will call the `loadFile` method, passing along the `$source` parameter.

```php
// Creating a new JImage object, passing it an existing handle.
$resource = imagecreate(100, 100);
$image = new JImage($resource);

// Creating a new JImage object, passing it an image path
$image = new JImage(JPATH_SITE . '/media/com_foo/images/uploads/bar.png');

// Creating a new JImage object then manually calling `loadFile`
$image = new JImage;
$image->loadFile(JPATH_SITE . '/media/com_foo/images/uploads/bar.png');
```

#### Usage

##### The `createThumbs` method
A common usage of the `JImage` class would be to resize uploaded images to thumbnails. Here is some example code for that.

```php
// Set the desired sizes for our thumbnails.
$sizes = array('300x300', '64x64', '250x125');

// Create our object
$image = new JImage(JPATH_SITE . '/media/com_foo/images/uploads/uploadedImage.jpg');

// Create the thumbnails
$image->createThumbs($sizes);
```

In this example, we use the `createThumbs` method of `JImage`. This method takes 2 parameters. The first parameter can be a string containing a single size in `WIDTHxHEIGHT` format, or it can be an array of sizes in the format (as shown in the example). The second parameter specifizes the resize method, and is one of the following:

* `JImage::SCALE_FILL` - Gives you a thumbnail of the exact size, stretched or squished to fit the parameters.
* `JImage::SCALE_INSIDE` - Fits your thumbnail within your given parameters. It will not be any taller or wider than the size passed, whichever is larger.
* `JImage::SCALE_OUTSIDE` - Fits your thumbnail to the given parameters. It will be as tall or as wide as the size passed, whichever is smaller.
* `JImage::CROP` - Gives you a thumbnail of the exact size, cropped from the center of the full sized image.
