<?php
        // src/AppBundle/Entity/User.php

        namespace AppBundle\Entity;

        use FOS\UserBundle\Model\User as BaseUser;
        use Doctrine\ORM\Mapping as ORM;

        use Symfony\Component\Validator\Constraints as Assert;
        use FR3D\LdapBundle\Model\LdapUserInterface;

        /**
         * @ORM\Entity
         * @ORM\Table(name="fos_user")
         */
        class User extends BaseUser implements LdapUserInterface        
        {
            /**
             * @ORM\Id
             * @ORM\Column(type="integer")
             * @ORM\GeneratedValue(strategy="AUTO")
             */
            protected $id;

               /**
             * @var string $nombre
             *
             * @ORM\Column(name="nombre", type="string", length=255, nullable=true)
             */
            protected $nombre;

            public function __construct()
            {
                parent::__construct();
                // your own logic
            }

            /**
             * Get id
             *
             * @return integer
             */
            public function getId()
            {
                return $this->id;
            }

         
            /**
             * Set nombre
             *
             * @param string $nombre
             */
            public function setNombre($nombre)
            {
                $this->nombre = $nombre;
            }
             
            /**
             * Get nombre
             *
             * @return string
             */
            public function getNombre()
            {
                return $this->nombre;
            }

            public function __toString()
            {
                return (string) $this->getNombre();
            }
            
            /**
             * Ldap Object Distinguished Name
             * @var string $dn
             */
            private $dn;

            /**
             * {@inheritDoc}
             */
            public function setDn($dn) {
                $this->dn = $dn;
            }

            /**
             * {@inheritDoc}
             */
            public function getDn() {
                return $this->dn;
            }
            
            public function getExpiresAt()
            {
                return $this->expiresAt;
            }
            
            public function getCredentialsExpireAt()
            {
                return $this->credentialsExpireAt;
            }          
            
         
          

        }


