# TODO: Align Database & App with UML Diagram

## Status: 🚀 In Progress (0/12 steps complete)

### Phase 1: Database Schema (Migrations & New Models)
- [x] 1. Create migration `create_agents_table.php` (nom, email, matricule, user_id)
- [x] 2. Create migration `create_controles_table.php` (vehicule_id, agent_id, lieu, place, heure, avenue, date, point_controle, conditions_meteo, observations)
- [x] 3. Create migration `create_bareme_prix_table.php` (code_infraction, libelle, montant_base, majoration_retard, delai_paiement)
- [x] 4. Create migration `add_controle_id_and_bareme_prix_id_to_contraventions_table.php`
- [x] 5. Create models: Agent.php, Controle.php, BaremePrix.php with relations/methods

### Phase 2: Update Existing Models
- [x] 6. Update User.php: add hasOne Agent
- [x] 7. Update Vehicule.php: add hasMany Controle
- [x] 8. Update Contravention.php: add belongsTo Controle, belongsTo BaremePrix (keep agent_id)

### Phase 3: Controllers & Views
- [x] 9. Update DashboardController.php: add Controle/Agent stats
- [x] 10. Update/create Agent/InfractionController.php: require Controle first
- [ ] 11. Update Admin controllers/views for new fields

### Phase 4: Finalize & Test
- [ ] 12. Update seeders, run `php artisan migrate:fresh --seed`, test relations in tinker

**Next: Step 1**
