import React from 'react';

const LegalMentionsPage: React.FC = () => (
  <>
    <div className="main-header">
      <h1>Mentions légales</h1>
    </div>
    <div className="legal-content">
      <h2>Éditeur du site</h2>
      <p><strong>SymfoX</strong><br />
        Réseau social éthique et conforme RGPD<br />
        Développé dans le cadre d'un projet éducatif</p>
      <h2>Hébergement</h2>
      <p>Ce site est hébergé localement dans un environnement de développement.</p>
      <h2>Technologies utilisées</h2>
      <ul>
        <li>Framework : Symfony 6</li>
        <li>Base de données : MySQL/PostgreSQL</li>
        <li>Frontend : Twig, CSS, JavaScript</li>
        <li>ORM : Doctrine</li>
      </ul>
      <h2>Propriété intellectuelle</h2>
      <p>L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.</p>
      <h2>Liens hypertextes</h2>
      <p>Les liens hypertextes mis en place dans le cadre du présent site web en direction d'autres ressources présentes sur le réseau Internet ne sauraient engager la responsabilité de SymfoX.</p>
      <h2>Contact</h2>
      <p>Pour toute question concernant ces mentions légales, vous pouvez nous contacter via le formulaire de contact disponible sur le site.</p>
      <p><em>Dernière mise à jour : {new Date().toLocaleDateString('fr-FR')}</em></p>
    </div>
  </>
);

export default LegalMentionsPage; 