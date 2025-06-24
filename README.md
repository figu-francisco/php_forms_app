# Context 

This project was developed as part of Francisco’s second year in the Bachelor's in Computer Science program at EPFC Brussels, Belgium (2024–2025).
As the project was assigned to teams of two, approximately 50% of the code can be attributed to Francisco. 

# Introduction 

The goal of this project is to develop an online form management application, inspired by tools like Google Forms or JotForm.
The project is built in PHP using a minimal framework provided by the instructors. For dynamic client-side behavior, it uses JavaScript and jQuery, and for styling, it uses Bootstrap.

Client-side dynamic behaviors are implemented only in the following views:

* Home Page (view_forms) 
* Manage Form (view_form) 
* Add/Edit Question (view_add_edit_question) 
* Form Stats (view_analyze) 

# Overview of Main Views 

## 1. Home Page (view_forms) 

This view displays all forms accessible to the user, sorted alphabetically by title. 

Each form is presented as a card showing: 

* The title 
* The description, if available 
* The creator's name 
* The start date of the most recent instance of this form for this user (or nothing if none exists). Dates are displayed as elapsed time (e.g., “3 days ago”), with a dynamic time scale.
* If there’s at least one instance: the submission date of the most recent one; otherwise, “in progress...” 
* The “Open” button allows users to fill out the form. It is hidden if the form contains no questions. 
* The “Manage” button opens the form in edit mode for users with editor rights. 
* The menu option “Add a new form” lets users create a new form. 

Anonymous users: 

* Can only view public forms 
* Cannot reopen existing instances; “Open” always starts a new instance
* No dates or instance statuses are shown 

### Dynamic Behavior on Home Page 

#### A. Full-text search 

A search bar appears above the list, allowing filtering by title, description, creator, and question content. 
The filtered results are updated dynamically and remain alphabetically sorted by title. 

#### B. Filters and Search 

Color-based search filters are available. 

When performing a search: 

* Only forms with at least one selected color are displayed, or all forms if no color is selected. 
* The results must also match the text search field. 
* The search (both text and color filters) works in both the pure PHP version (without JavaScript, using the PRG pattern) and the dynamic client-side version (with JavaScript). 

In the PHP version, a button is used to submit the search (this button is hidden when JavaScript is enabled). 

#### C. Preserving search filter across navigation 

When opening a form from a filtered list (via “Open” or “Manage”) and navigating through its pages, clicking “Back” should return to the same filtered state with the same search term applied. 

This is implemented without using $_SESSION or cookies, which are not allowed due to shared state across browser tabs.

 

## 2.  Instance Page (view_add_edit_instance) 

#### New instance: 

* Users navigate through questions in order using arrows 
* Answers (valid or not) are saved in the database as the user navigates 
* If a required question is unanswered (except when opened for the first time), an error is shown 
* Previously answered questions are prefilled and can be modified
* Invalid answers show validation messages
* The user can leave the form anytime using the “x” button 

#### Ongoing instance: 

* If a user reopens an in-progress instance, previously entered answers are shown and editable 
* Anonymous users cannot reopen existing instances (in progress or submitted) 

#### Submission: 

* A “Save” button appears on the last question 
* If any answers are invalid, a message is displayed and the user is redirected to the first error ; the form is not submitted 
* If all validations pass, the instance is submitted and a confirmation message is shown

#### Submitted instance: 

* Answers are shown in read-only mode 

## 3. Manage Form (view_form) 

#### Displays detailed information about the form: 

* Top section: Form metadata 
* Bottom section: List of questions in index order 

#### If there are no instances: 

* “Edit” button allows updating form metadata 
* “Add” button allows users to add a question 
* Questions can be edited or deleted 
* Arrow buttons on question cards reorder questions, with changes saved immediately 

#### If at least one instance exists: 

* The form becomes read-only, even if no instances are submitted (in progress)

* If at least one submitted instance exists:
  * A button appears to open the statistics page analyzing user responses 
  * A button appears to view all submitted instances 

* If any instance exists (submitted or not), a button allows deleting all instances, with a confirmation prompt

#### In all cases: 

* A button allows deleting the form and all related data 

* A “Public” toggle lets users switch the form between public and private 

### Dynamic Behavior on Manage Form Page 

Validation for adding or editing questions is handled client-side (both synchronously and asynchronously), using AJAX when necessary.

#### Validation rules: 

* The trimmed title must be within length limits defined in dev.ini 
* The title must be unique per form 
* The description, if provided, must also be within the specified limits
* The question type is required 

#### Validation behavior: 

* Triggered on keypress 
* Fields with errors are highlighted in red, with error messages shown below 
* Valid fields are marked in green 
* The Save button is disabled if any validation errors are present 

### Liste des utilisateurs et mots de passes

* admin@demo.com, password "Password1,", admin
* joe.doe@demo.com, password "Password1,", user
* jane.smith@demo.com, password "Password1,", user
* john.roe@demo.com, password "Password1,", user
* mary.major@demo.com, password "Password1,", user
* guest@demo.eu, password "Password1,", guest

### IP deployment

A demo of the project can be found on franciscofigueroa.dev/formsapp 
Feel free to explore. Keep in mind that I haven't yet gotten a domain name nor a SSL certificate. Don't use real data while interacting with the app. 
