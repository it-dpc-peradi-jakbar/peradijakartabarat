# Agent instructions — peradijakartabarat

Laravel app for DPC PERADI Jakarta Barat internship platform (Kantong Magang Advokat). Local setup: [README.md](README.md).

## Jarvis hub

Workstation, domain glossary, graphify, and plans live in **`~/projects/jarvis`**:

- Repo path: `PERADIJAKARTABARAT_PATH` in jarvis `.env` (manifest: `workstation/repos.manifest`)
- Glossary: jarvis `CONTEXT.md` (roles, entities, Indonesian product terms)
- Index hub (graphify + archify): `make learn REPO=peradijakartabarat` from jarvis root (or `jarvis learn peradijakartabarat`)
- Runtime diagram: `make open SERVICE=peradijakartabarat` (from jarvis root; `archify/peradijakartabarat/runtime.architecture.json`)
- Coding standards: `~/projects/jarvis/.agents/skills/development/SKILL.md`

Prefix chat commands with **`jarvis`** (e.g. `jarvis analyze verification flow`) when the jarvis hub is in the workspace.
