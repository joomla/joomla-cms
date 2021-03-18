<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Webauthn\MetadataService;

abstract class AuthenticatorStatus
{
    public const NOT_FIDO_CERTIFIED = 'NOT_FIDO_CERTIFIED';
    public const FIDO_CERTIFIED = 'FIDO_CERTIFIED';
    public const USER_VERIFICATION_BYPASS = 'USER_VERIFICATION_BYPASS';
    public const ATTESTATION_KEY_COMPROMISE = 'ATTESTATION_KEY_COMPROMISE';
    public const USER_KEY_REMOTE_COMPROMISE = 'USER_KEY_REMOTE_COMPROMISE';
    public const USER_KEY_PHYSICAL_COMPROMISE = 'USER_KEY_PHYSICAL_COMPROMISE';
    public const UPDATE_AVAILABLE = 'UPDATE_AVAILABLE';
    public const REVOKED = 'REVOKED';
    public const SELF_ASSERTION_SUBMITTED = 'SELF_ASSERTION_SUBMITTED';
    public const FIDO_CERTIFIED_L1 = 'FIDO_CERTIFIED_L1';
    public const FIDO_CERTIFIED_L1plus = 'FIDO_CERTIFIED_L1plus';
    public const FIDO_CERTIFIED_L2 = 'FIDO_CERTIFIED_L2';
    public const FIDO_CERTIFIED_L2plus = 'FIDO_CERTIFIED_L2plus';
    public const FIDO_CERTIFIED_L3 = 'FIDO_CERTIFIED_L3';
    public const FIDO_CERTIFIED_L3plus = 'FIDO_CERTIFIED_L3plus';
}
