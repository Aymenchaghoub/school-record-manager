const fr = {
  common: {
    actions: {
      create: 'Creer',
      edit: 'Modifier',
      delete: 'Supprimer',
      save: 'Enregistrer',
      cancel: 'Annuler',
      refresh: 'Rafraichir',
      previous: 'Precedent',
      next: 'Suivant',
      load: 'Charger',
      add: 'Ajouter',
      remove: 'Retirer',
    },
    labels: {
      actions: 'Actions',
      total: 'Total',
      page: 'Page',
      of: 'sur',
      yes: 'Oui',
      no: 'Non',
    },
    feedback: {
      loading: 'Chargement...',
      loadingData: 'Chargement des donnees...',
      creating: 'Creation en cours...',
      updating: 'Mise a jour en cours...',
      createdSuccess: 'Creation enregistree avec succes.',
      updatedSuccess: 'Modification enregistree avec succes.',
      deletedSuccess: 'Suppression effectuee avec succes.',
    },
    emptyState: {
      noResultsTitle: 'Aucun resultat',
      noResultsDescription: 'Ajustez vos filtres ou creez un nouvel enregistrement.',
    },
    confirm: {
      deleteTitle: 'Confirmer la suppression',
      deleteMessage:
        'Etes-vous sur de vouloir supprimer cet enregistrement ? Cette action est irreversible.',
    },
    errors: {
      load: 'Erreur de chargement',
      save: 'Erreur pendant la sauvegarde',
      delete: 'Suppression impossible',
      emailInvalid: 'Entrez une adresse email valide, ex: nom@ecole.fr.',
      fixFields: 'Corrigez les champs signales avant de continuer.',
      generic: 'Une erreur est survenue',
    },
  },
  topBar: {
    searchPlaceholder: 'Rechercher un eleve, une classe...',
    profileFallbackName: 'Utilisateur',
    menuLabel: 'Basculer le menu de navigation',
    notificationsLabel: 'Voir les notifications',
    profileLabel: 'Voir mon profil',
    themeToLight: 'Activer le mode clair',
    themeToDark: 'Activer le mode sombre',
    modeLight: 'Mode clair',
    modeDark: 'Mode sombre',
  },
  login: {
    errors: {
      failed: 'Echec de connexion.',
    },
  },
  profile: {
    success: 'Profil mis a jour avec succes.',
    errors: {
      firstNameRequired: 'Renseignez votre prenom.',
      lastNameRequired: 'Renseignez votre nom.',
      emailRequired: 'Renseignez votre adresse email.',
      passwordMin: 'Le mot de passe doit contenir au moins 8 caracteres.',
      passwordConfirmRequired: 'Confirmez le nouveau mot de passe.',
      passwordConfirmMismatch: 'La confirmation ne correspond pas au mot de passe.',
      passwordMissing: 'Saisissez d abord un nouveau mot de passe.',
      updateFailed: 'Une erreur est survenue',
    },
  },
  dashboards: {
    admin: {
      kpis: {
        students: 'Total eleves',
        teachers: 'Total enseignants',
        average: 'Moyenne generale',
        absences: 'Absences du mois',
      },
      charts: {
        studentsPerClass: 'Eleves par classe',
        averagePerSubject: 'Moyenne par matiere',
        absencesPerMonth: 'Absences par mois',
        gradesEvolution: 'Evolution des notes',
      },
      forms: {
        student: 'Eleve',
      },
      emptyState: {
        noDataTitle: 'Aucune donnee',
        studentsPerClassDescription:
          'La repartition des eleves apparaitra des que les classes auront des inscriptions.',
        averagePerSubjectDescription:
          'Les moyennes par matiere apparaitront des que des notes seront enregistrees.',
        absencesPerMonthDescription:
          'La tendance des absences apparaitra lorsque des absences seront enregistrees.',
        evolutionUnavailable:
          'Impossible de charger l evolution des notes pour l eleve selectionne.',
        selectStudent: 'Selectionnez un eleve',
        selectStudentDescription:
          'Choisissez un eleve puis cliquez sur Charger pour afficher son evolution.',
        noEvolutionPoint:
          'Aucun point d evolution disponible pour l eleve selectionne.',
      },
    },
  },
  tables: {
    users: {
      name: 'Nom',
      email: 'Email',
      role: 'Role',
      active: 'Actif',
      createdAt: 'Creation',
    },
    classes: {
      name: 'Nom',
      code: 'Code',
      level: 'Niveau',
      section: 'Section',
      year: 'Annee',
      capacity: 'Capacite',
      status: 'Etat',
    },
    subjects: {
      name: 'Nom',
      code: 'Code',
      type: 'Type',
      credits: 'Credits',
      teacher: 'Enseignant',
      status: 'Etat',
    },
    grades: {
      student: 'Eleve',
      subject: 'Matiere',
      class: 'Classe',
      grade: 'Note',
      type: 'Type',
      term: 'Periode',
      date: 'Date',
    },
    absences: {
      student: 'Eleve',
      class: 'Classe',
      type: 'Type',
      date: 'Date',
      justified: 'Justifiee',
      reason: 'Motif',
    },
    events: {
      title: 'Titre',
      type: 'Type',
      start: 'Debut',
      end: 'Fin',
      location: 'Lieu',
      published: 'Publie',
    },
    reportCards: {
      student: 'Eleve',
      class: 'Classe',
      term: 'Periode',
      year: 'Annee',
      average: 'Moyenne',
      perSubjectAverages: 'Moyennes par matiere',
      absences: 'Absences',
      final: 'Final',
    },
  },
  pages: {
    events: {
      errors: {
        invalidAudience: 'Le champ audience cible est invalide.',
      },
    },
    reportCards: {
      errors: {
        invalidSubjectGrades: 'Le champ notes par matiere est invalide.',
      },
    },
  },
};

export default fr;
