# Infrastructure Layer (QueryServices)

## Overview

Infra is the layer responsible for connecting to the database and handling persistence. In an onion architecture, it occupies the outermost layer. However, by utilising the interfaces in the Domain Layer, it achieves database access without depending on concrete implementations, by depending on interfaces defined in the Domain Layer.

---

## Directory Structure

```
Domains/
├── Adapter/    # AlgoliaWorldHeritageSearchAdapter - Handles communication with Algolia
├── Infra/      # QueryService and QueryReadService - Database access
├── Ports/      # Define contracts for external services and return value objects
```

---

## Responsibilities

### QueryService
- An infrastructure layer service written for read operations, based on CQRS principles. However, the interface is placed in the Application Layer. The reason for this is that the results of the QueryService depend on the requirements of the UseCase; by designing the data flow around the UseCase, we achieve flexible and consistent behaviour.

### QueryServiceInterface
- Defines the contracts for the Domain Layer
- The Application Layer depends on these interfaces, not on concrete implementations
- Enables dependency inversion between the Application Layer and the Domain Layer

---

## Layer Boundaries

| Allowed | Not Allowed |
|---|---|
| Access to Gateway via interface | Access to Gateway directly |
| Receive ListQuery | Receive primitive types |
| Return HeritageSearchResult | Return primitive types |

---

## Data Flow

```
UseCase
    ↓
QueryService
    ↓
AlgoliaAdapter
    ↓ (DTO)
SearchResult (Ports)
    ↓
Dto
```