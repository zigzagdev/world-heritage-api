# Application Layer (QueryUseCases)

## Overview

The Application Layer is responsible for orchestrating the business logic of the application.
It acts as the bridge between the Presentation Layer and the Domain Layer.

This layer **must not** contain any infrastructure concerns such as database access or external API calls.
All such operations are delegated to the Domain Layer via interfaces.

---

## Directory Structure

```
QueryUseCases/
├── Dto/                       # Data Transfer Objects passed between layers
├── Factory/
│   └── ListQuery/             # Build ListQuery objects from primitive types
├── ListQuery/                 # Represent the input parameters for a query
├── QueryServiceInterface/     # Interfaces for the Domain Layer
├── Tests/                     # Unit tests for the Application Layer
├── UseCase/                   # Orchestrate the business logic
└── ViewModel/                 # Represent the response data structure for the Presentation Layer
```

---

## Responsibilities

### UseCase
- Orchestrate the business logic by coordinating the Domain Layer
- Receive **primitive types** (`string`, `int`, `null`) from the Presentation Layer
- Build a `ListQuery` object via the corresponding `Factory`
- Pass the `ListQuery` to the Domain Layer (QueryService)
- Return a `PaginationDto` to the Presentation Layer
- **Must not** contain any infrastructure concerns

### ListQuery
- Represent the validated and type-safe input parameters for a query
- Holds domain-specific types such as `StudyRegion` Enum instead of raw strings
- **Must not** contain any business logic

### Factory (ListQuery)
- Build a `ListQuery` object from primitive types received from the UseCase
- Responsible for converting raw strings to domain types (e.g. `StudyRegion` Enum)
- Throws `InvalidArgumentException` if an invalid value is provided

### Dto
- Represent the output data passed from the Domain Layer to the Presentation Layer
- A simple data object with no business logic

### QueryServiceInterface
- Define the contracts for the Domain Layer
- The Application Layer depends on these interfaces, not on concrete implementations
- Enables dependency inversion between the Application Layer and the Domain Layer

### ViewModel
- Represent the response data structure for the Presentation Layer
- Built from a `Dto` via the corresponding `Factory`

---

## Layer Boundaries

| Allowed | Not Allowed |
|---|---|
| Receiving primitives from the Presentation Layer | Direct database access |
| Building `ListQuery` via Factory | Direct external API calls |
| Calling Domain Layer via interfaces | Accessing infrastructure implementations directly |
| Returning `Dto` to the Presentation Layer | Containing business logic |

---

## Data Flow

```
Presentation Layer (Controller)
    ↓ (primitive types: string, int, null)
UseCase
    ↓ (primitive types)
ListQueryFactory
    ↓ (ListQuery with domain types e.g. StudyRegion Enum)
QueryService (Domain Layer)
    ↓ (Dto)
UseCase
    ↓ (Dto)
Presentation Layer (Controller)
```

---

## Validation Strategy

Input validation is handled at the `Factory` level when building a `ListQuery`.

- **Valid value** → Converted to the corresponding domain type (e.g. `StudyRegion::ASIA`)
- **Null value** → Passed through as `null` (nullable fields are allowed)
- **Invalid value** → Throws `InvalidArgumentException`

This ensures that by the time a `ListQuery` reaches the Domain Layer, all values are guaranteed to be valid.