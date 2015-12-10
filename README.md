# Field Formatter

[Field Formatter](https://www.drupal.org/project/field_formatter) module adds formatters for entity reference fields to output only a specific field or field value using a specific formatter w/o needing to configure media view mode and configuring it to display properly. This module provides two formatters: *Field formatter with inline settings* and *Field formatter from view display*.

## Installation

1. Download [Field Formatter](https://www.drupal.org/project/field_formatter) from [GitHub](https://github.com/drupal-media/field_formatter).
2. Install it in the [usual way](https://www.drupal.org/documentation/install/modules-themes/modules-8).

## Usage

1. Create entity reference field
  * On `admin/structure` choose **Content types**
  * Choose content type on which you want to use entity reference field, for example: *Article*, and click **Manage fields** and then **+ Add field**
  * From **References** menu choose **Other**, fill the *Label* and click **Save and continue**
  * Choose **Type of item to reference**, for example: *Content*, and click **Save field settings**
  * Select which content types you want to reference in **Reference type section**, for example: *Article*, and click **Save settings**
2. Choose field formatter for your entity reference field
  * On your content type open Manage display (in our case *Article*), on `admin/structure/types/manage/article/display`
  * Choose which formatter you want to use, *Field formatter with inline settings* or *Field formatter from view display*
  * In formatter settings in *Field name* choose field you want to display from referenced entity in your article
  * If you use *Field formatter from view display* beside field you must choose *View mode* also
  * Click **Update** and then **Save**
3. Create a new node (in our case *Article*) with entity reference field
  * For your entity reference field choose entity (or in our case *Article*) you want to reference
  * The chosen field from referenced entity is displayed on the saved article page
