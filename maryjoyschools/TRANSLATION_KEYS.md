# Translation Keys for Student Management

The following translation keys need to be added to the system for the student management functionality:

## Student-Specific Keys

### Basic Information
- `StudentName` - "Student Name"
- `StudentID` - "Student ID"
- `AdmissionNumber` - "Admission Number"
- `ClassGrade` - "Class/Grade"
- `Stream` - "Stream"
- `House` - "House"
- `DateOfBirth` - "Date of Birth"
- `Gender` - "Gender"
- `Status` - "Status"

### Parent/Guardian Information
- `ParentGuardianName` - "Parent/Guardian Name"
- `ParentGuardianPhone` - "Parent/Guardian Phone"
- `ParentGuardianEmail` - "Parent/Guardian Email"
- `EmergencyContact` - "Emergency Contact"

### Financial Information
- `AccountBalance` - "Account Balance"
- `CreditLimit` - "Credit Limit"
- `AvailableCredit` - "Available Credit"

### Form Labels and Placeholders
- `SelectClass` - "Select Class"
- `SelectStream` - "Select Stream"
- `SelectGender` - "Select Gender"
- `SelectStatus` - "Select Status"

### Page Titles and Navigation
- `StudentManagement` - "Student Management"
- `Students` - "Students"
- `StudentDetails` - "Student Details"
- `StudentCode` - "Student Code"
- `StudentPhone` - "Student Phone"
- `StudentEmail` - "Student Email"

### Additional Fields
- `Notes` - "Notes"

## How to Add These Translations

These translation keys need to be added to the database through the admin panel:

1. Go to Settings > Language Settings
2. Select the language you want to add translations for
3. Add each key with its corresponding translation
4. Save the changes

## Default Values

For English (en), use the values provided above. For other languages, translate accordingly.

## Usage in Vue Components

These keys are used in the Vue component with the `$t()` function:
```javascript
$t('StudentName')  // Returns "Student Name"
$t('ClassGrade')   // Returns "Class/Grade"
```

The system will fall back to the key name if no translation is found.