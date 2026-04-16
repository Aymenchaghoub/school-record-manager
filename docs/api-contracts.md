# API Contracts - School Record Manager v1

## Authentication
### POST /api/v1/login
Body:
```json
{ "email": "string", "password": "string" }
```
Response 200:
```json
{ "message": "Login successful.", "user": { "id": 1, "role": "admin" } }
```
Response 422:
```json
{ "message": "The given data was invalid.", "errors": { "email": ["..."] } }
```

### POST /api/v1/logout
Headers: cookie Sanctum session
Response 200:
```json
{ "message": "Logged out successfully." }
```

### GET /api/v1/user
Headers: cookie Sanctum session
Response 200:
```json
{ "user": { "id": 1, "name": "...", "role": "admin" } }
```

## Grades (Admin)
### GET /api/v1/admin/grades
Response 200:
```json
{
  "success": true,
  "message": "Grades fetched successfully.",
  "data": {
    "items": [
      {
        "id": 1,
        "student_id": 10,
        "subject_id": 5,
        "class_id": 3,
        "teacher_id": 2,
        "value": "14.00",
        "max_value": "20.00",
        "type": "exam",
        "term": "Term 1"
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

### POST /api/v1/admin/grades
Body:
```json
{
  "student_id": 10,
  "subject_id": 5,
  "class_id": 3,
  "teacher_id": 2,
  "value": 14,
  "type": "exam",
  "grade_date": "2026-04-16",
  "term": "Term 1",
  "weight": 1
}
```
Response 201:
```json
{
  "success": true,
  "message": "Grade created successfully.",
  "data": {
    "id": 1,
    "value": "14.00",
    "max_value": "20.00",
    "type": "exam"
  }
}
```

### GET /api/v1/admin/grades/{id}
Response 200: `GradeResource`

### PUT /api/v1/admin/grades/{id}
Response 200: `GradeResource`

### DELETE /api/v1/admin/grades/{id}
Response 200:
```json
{ "success": true, "message": "Grade deleted successfully.", "data": null }
```

## Grade Resource
```json
{
  "id": 1,
  "value": "14.00",
  "max_value": "20.00",
  "type": "exam",
  "term": "Term 1",
  "student": { "id": 10, "name": "..." },
  "subject": { "id": 5, "name": "..." },
  "created_at": "2026-04-16T10:00:00Z"
}
```

## Role-scoped endpoints (v1)
- Admin: `/api/v1/admin/*`
- Teacher: `/api/v1/teacher/*`
- Student: `/api/v1/student/*`
- Parent: `/api/v1/parent/*`
- Dashboard KPIs: `/api/v1/dashboard/*`
