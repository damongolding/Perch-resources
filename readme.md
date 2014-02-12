# Perch Resources

Just a collection of resources for [Perch CMS](https://grabaperch.com/ "Perch")

## Field Types

Place these in addons/fieldtypes

### base64

Outputs a image as a base64 sting (e.g data:image/.png;base64,iVBORw0KGgoAAAANSUh...) will also respect normal image tags (crop, width, desity etc)

example:
    `<perch:content type="base64img" id="base64" label="base64" />`


### base64blur

#### requires [imagemagick](http://www.imagemagick.org/)

Outputs a blurred img as a base64 sting (e.g data:image/.png;base64,iVBORw0KGgoAAAANSUh...) will also respect normal image tags (crop, width, desity etc)

example:
    `<perch:content type="base64blur" id="blur" label="blurrd image" bluramount="1" />`

