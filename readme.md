# Perch Resources

Just a collection of resources for [Perch CMS](https://grabaperch.com/ "Perch") I have either made or modded.

### List of contents
* [Field Types](#field-types)
    * [base64](#base64)
    * [Colour Picker](#colour-picker)
* [Editors](#editors)
    * [Redactor character limiter](#redactor-character-limiter)
* [UI tweaks](#ui-tweaks)
    * [Hidden region indicator](#hidden-region-indicator)


## Field Types

Place these in addons/fieldtypes

### base64

Outputs a image as a base64 sting (e.g data:image/.png;base64,iVBORw0KGgoAAAANSUh...) will also respect normal image type attributes (crop, width, desity etc).

**example use in content templates:**  
`<img src="<perch:content type="base64img" id="" label="" />" alt="" />`


### base64blur

##### requires [imagemagick](http://www.imagemagick.org/)

Outputs a blurred img as a base64 sting (e.g data:image/.png;base64,iVBORw0KGgoAAAANSUh...) will also respect normal image type attributes (crop, width, desity etc).

##### Options
- Blur amount (1 = the lowest)

**example use in content templates:**  
`<img src="<perch:content type="base64blur" id="" label="" bluramount="1" />" alt="" />`

### Colour Picker

outputs a HEX ref using the (amazing) [iris](http://automattic.github.io/Iris/) colourpicker

**example use in content templates:**  
`<perch:content type="colour" id="" label="" />`


## Editors

Place these in addons/editors.

### Redactor character limiter

##### * This addon does not come with the redactor editer itself. You must have the redactor (1.6) editor installed/added in the addons/editors folder for this to work. *


Redactor editor but with a character count and limiter. This editor is a little bit of a hack. Rather then creating a custom tag, I am using the size tag to pass a class which contains the character limit to the textarea field in the editor.

**example useage:**  
Lets say we want to add a 250 character limit to the textarea. To do this we would need to add `size="charlimit-250"`. For a 300 limit `size="charlimit-300"` and so on. We would also need to set the editor tag to `editor="redactor-limiter"`.

**Example usage in a template tag:**  
`<perch:content type='textarea' id="" label="" html="true" editor="redactor-limiter" size="charlimit-250" />`


## UI tweaks

### Hidden region indicator

##### Word of warning. This was is build for a spesific need, therefore it is not very robust. By all mean use/mod this but if you upgrade it drop me a line :)

This tweak inserts a new column into the region table on the page edit page. It was build for a region that does not use multiple items.
To use include the php script to your UI include file in 'perch/addons/plugins/ui/_config.inc' (example file included).

**Example:**  
A example content templet is included in the 'content' folder. Basicaly you ould wrap your conent in a perch:if statement. If the script does not find the
 the perch:if statement it will display the region as 'displayed'.






