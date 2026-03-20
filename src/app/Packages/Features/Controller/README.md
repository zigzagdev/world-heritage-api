# Presentation Layer

## Overview

The Presentation Layer is responsible for handling HTTP requests and returning HTTP responses.
It acts as the gateway between the client and the Application Layer.

This layer **must not** contain any business logic. It delegates all processing to the Application Layer (UseCase) and formats the result for the client.

---

## Directory Structure

```
Http/
├── Controllers/       # Handle HTTP requests and return JSON responses
├── ViewModels/        # Represent the response data structure for a single resource
├── ViewModelCollections/ # Represent the response data structure for a collection of resources
└── Factories/
    ├── ViewModelFactory/           # Build a single ViewModel from a DTO
    └── ViewModelCollectionFactory/ # Build a ViewModelCollection from a DtoCollection
```

---

## Responsibilities

### Controller
- Receive HTTP requests and extract parameters (query string, path parameters, request body)
- Pass **primitive types** (`string`, `int`, `null`) to the UseCase
- Return a JSON response with the result from the UseCase
- **Must not** create `ListQuery` objects or any Application Layer concepts

### ViewModel
- Represent the response data structure for a single resource
- A simple data object that holds the formatted values for the client
- **Must not** contain any business logic

### ViewModelCollection
- Represent the response data structure for a collection of resources
- Wraps multiple `ViewModel` instances

### Factory (ViewModel / ViewModelCollection)
- Build `ViewModel` or `ViewModelCollection` from a `Dto` or `DtoCollection`
- Responsible for mapping Application Layer data to Presentation Layer data

---

## Layer Boundaries

| Allowed | Not Allowed |
|---|---|
| Receiving HTTP requests | Business logic |
| Passing primitives to UseCase | Creating `ListQuery` objects |
| Formatting DTO into ViewModel | Accessing the Domain Layer directly |
| Returning JSON responses | Accessing the Infrastructure Layer directly |

---

## Data Flow

```
HTTP Request
    ↓
Controller
    ↓ (primitive types: string, int, null)
UseCase (Application Layer)
    ↓ (DTO)
ViewModelFactory
    ↓ (ViewModel)
JSON Response
```