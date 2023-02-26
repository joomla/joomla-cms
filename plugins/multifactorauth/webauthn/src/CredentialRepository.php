<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Webauthn;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use RuntimeException;
use Webauthn\AttestationStatement\AttestationStatement;
use Webauthn\AttestedCredentialData;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TrustPath\EmptyTrustPath;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Implementation of the credentials repository for the WebAuthn library.
 *
 * Important assumption: interaction with Webauthn through the library is only performed for the currently logged in
 * user. Therefore all Methods which take a credential ID work by checking the Joomla MFA records of the current
 * user only. This is a necessity. The records are stored encrypted, therefore we cannot do a partial search in the
 * table. We have to load the records, decrypt them and inspect them. We cannot do that for thousands of records but
 * we CAN do that for the few records each user has under their account.
 *
 * This behavior can be changed by passing a user ID in the constructor of the class.
 *
 * @since 4.2.0
 */
class CredentialRepository implements PublicKeyCredentialSourceRepository
{
    /**
     * The user ID we will operate with
     *
     * @var   integer
     * @since 4.2.0
     */
    private $userId = 0;

    /**
     * CredentialRepository constructor.
     *
     * @param   int  $userId  The user ID this repository will be working with.
     *
     * @throws \Exception
     * @since 4.2.0
     */
    public function __construct(int $userId = 0)
    {
        if (empty($userId)) {
            $user = Factory::getApplication()->getIdentity()
                ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

            $userId = $user->id;
        }

        $this->userId = $userId;
    }

    /**
     * Finds a WebAuthn record given a credential ID
     *
     * @param   string  $publicKeyCredentialId  The public credential ID to look for
     *
     * @return  PublicKeyCredentialSource|null
     * @since   4.2.0
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $publicKeyCredentialUserEntity = new PublicKeyCredentialUserEntity('', $this->userId, '', '');
        $credentials                   = $this->findAllForUserEntity($publicKeyCredentialUserEntity);

        foreach ($credentials as $record) {
            if ($record->getAttestedCredentialData()->getCredentialId() != $publicKeyCredentialId) {
                continue;
            }

            return $record;
        }

        return null;
    }

    /**
     * Find all WebAuthn entries given a user entity
     *
     * @param   PublicKeyCredentialUserEntity  $publicKeyCredentialUserEntity The user entity to search by
     *
     * @return  array|PublicKeyCredentialSource[]
     * @throws  \Exception
     * @since   4.2.0
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        if (empty($publicKeyCredentialUserEntity)) {
            $userId = $this->userId;
        } else {
            $userId = $publicKeyCredentialUserEntity->getId();
        }

        $return = [];

        $results = MfaHelper::getUserMfaRecords($userId);

        if (count($results) < 1) {
            return $return;
        }

        /** @var MfaTable $result */
        foreach ($results as $result) {
            $options = $result->options;

            if (!is_array($options) || empty($options)) {
                continue;
            }

            if (!isset($options['attested']) && !isset($options['pubkeysource'])) {
                continue;
            }

            if (isset($options['attested']) && is_string($options['attested'])) {
                $options['attested'] = json_decode($options['attested'], true);

                $return[$result->id] = $this->attestedCredentialToPublicKeyCredentialSource(
                    AttestedCredentialData::createFromArray($options['attested']),
                    $userId
                );
            } elseif (isset($options['pubkeysource']) && is_string($options['pubkeysource'])) {
                $options['pubkeysource'] = json_decode($options['pubkeysource'], true);
                $return[$result->id]     = PublicKeyCredentialSource::createFromArray($options['pubkeysource']);
            } elseif (isset($options['pubkeysource']) && is_array($options['pubkeysource'])) {
                $return[$result->id] = PublicKeyCredentialSource::createFromArray($options['pubkeysource']);
            }
        }

        return $return;
    }

    /**
     * Converts a legacy AttestedCredentialData object stored in the database into a PublicKeyCredentialSource object.
     *
     * This makes several assumptions which can be problematic and the reason why the WebAuthn library version 2 moved
     * away from attested credentials to public key credential sources:
     *
     * - The credential is always of the public key type (that's safe as the only option supported)
     * - You can access it with any kind of authenticator transport: USB, NFC, Internal or Bluetooth LE (possibly
     * dangerous)
     * - There is no attestations (generally safe since browsers don't seem to support attestation yet)
     * - There is no trust path (generally safe since browsers don't seem to provide one)
     * - No counter was stored (dangerous since it can lead to replay attacks).
     *
     * @param   AttestedCredentialData  $record  Legacy attested credential data object
     * @param   int                     $userId  User ID we are getting the credential source for
     *
     * @return  PublicKeyCredentialSource
     * @since   4.2.0
     */
    private function attestedCredentialToPublicKeyCredentialSource(AttestedCredentialData $record, int $userId): PublicKeyCredentialSource
    {
        return new PublicKeyCredentialSource(
            $record->getCredentialId(),
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            [
                PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_USB,
                PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_NFC,
                PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_INTERNAL,
                PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_BLE,
            ],
            AttestationStatement::TYPE_NONE,
            new EmptyTrustPath(),
            $record->getAaguid(),
            $record->getCredentialPublicKey(),
            $userId,
            0
        );
    }

    /**
     * Save a WebAuthn record
     *
     * @param   PublicKeyCredentialSource  $publicKeyCredentialSource  The record to save
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        // I can only create or update credentials for the user this class was created for
        if ($publicKeyCredentialSource->getUserHandle() != $this->userId) {
            throw new RuntimeException('Cannot create or update WebAuthn credentials for a different user.', 403);
        }

        // Do I have an existing record for this credential?
        $recordId                      = null;
        $publicKeyCredentialUserEntity = new PublicKeyCredentialUserEntity('', $this->userId, '', '');
        $credentials                   = $this->findAllForUserEntity($publicKeyCredentialUserEntity);

        foreach ($credentials as $id => $record) {
            if ($record->getAttestedCredentialData()->getCredentialId() != $publicKeyCredentialSource->getAttestedCredentialData()->getCredentialId()) {
                continue;
            }

            $recordId = $id;

            break;
        }

        // Create or update a record
        /** @var MVCFactoryInterface $factory */
        $factory = Factory::getApplication()->bootComponent('com_users')->getMVCFactory();
        /** @var MfaTable $mfaTable */
        $mfaTable = $factory->createTable('Mfa', 'Administrator');

        if ($recordId) {
            $mfaTable->load($recordId);

            $options = $mfaTable->options;

            if (isset($options['attested'])) {
                unset($options['attested']);
            }

            $options['pubkeysource'] = $publicKeyCredentialSource;
            $mfaTable->save(
                [
                    'options' => $options
                ]
            );
        } else {
            $mfaTable->reset();
            $mfaTable->save(
                [
                    'user_id' => $this->userId,
                    'title'   => 'WebAuthn auto-save',
                    'method'  => 'webauthn',
                    'default' => 0,
                    'options' => ['pubkeysource' => $publicKeyCredentialSource],
                ]
            );
        }
    }
}
