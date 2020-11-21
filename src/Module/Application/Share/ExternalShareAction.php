<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Share;

use Ampache\Config\AmpConfig;
use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Model\Plugin;
use Ampache\Model\Share;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\System\Core;
use Ampache\Module\System\LegacyLogger;
use Ampache\Module\User\PasswordGenerator;
use Ampache\Module\User\PasswordGeneratorInterface;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Teapot\StatusCode;

final class ExternalShareAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'external_share';

    private ConfigContainerInterface $configContainer;

    private UiInterface $ui;

    private LoggerInterface $logger;

    private PasswordGeneratorInterface $passwordGenerator;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ConfigContainerInterface $configContainer,
        UiInterface $ui,
        LoggerInterface $logger,
        PasswordGeneratorInterface $passwordGenerator,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->configContainer   = $configContainer;
        $this->ui                = $ui;
        $this->logger            = $logger;
        $this->passwordGenerator = $passwordGenerator;
        $this->responseFactory   = $responseFactory;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (!$this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::SHARE)) {
            $this->logger->warning(
                'Access Denied: sharing features are not enabled.',
                [LegacyLogger::CONTEXT_TYPE => __CLASS__]
            );
            $this->ui->accessDenied();

            return null;
        }

        if ($this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE)) {
            $this->ui->accessDenied();

            return null;
        }


        $plugin = new Plugin(Core::get_get('plugin'));
        if (!$plugin) {
            $this->ui->accessDenied('Access Denied - Unknown external share plugin');

            return null;
        }
        $plugin->load(Core::get_global('user'));

        $type           = Core::get_request('type');
        $share_id       = Core::get_request('id');
        $allow_download = (($type == 'song' && Access::check_function('download')) || Access::check_function('batch_download'));
        $secret         = $this->passwordGenerator->generate(PasswordGenerator::DEFAULT_LENGTH);

        $share_id = Share::create_share($type, $share_id, true, $allow_download, AmpConfig::get('share_expire'), $secret, 0);
        $share    = new Share($share_id);
        $share->format(true);

        return $this->responseFactory
            ->createResponse(StatusCode::FOUND)
            ->withHeader(
                'Location',
                $plugin->_plugin->external_share($share->public_url, $share->f_name)
            );
    }
}