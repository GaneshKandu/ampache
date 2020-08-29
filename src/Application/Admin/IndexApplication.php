<?php

declare(strict_types=0);

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

namespace Ampache\Application\Admin;

use Ampache\Module\Authorization\Access;
use Ampache\Application\ApplicationInterface;
use Browse;
use Catalog;
use Ampache\Module\Util\Ui;

final class IndexApplication implements ApplicationInterface
{
    public function run(): void
    {
        if (!Access::check('interface', 75)) {
            Ui::access_denied();

            return;
        }

        Ui::show_header();

        // Switch on the actions
        switch ($_REQUEST['action']) {
            default:
                // Show Catalogs
                $catalog_ids = Catalog::get_catalogs();
                $browse      = new Browse();
                $browse->set_type('catalog');
                $browse->set_static_content(true);
                $browse->save_objects($catalog_ids);
                $browse->show_objects($catalog_ids);
                $browse->store();
                break;
        }

        // Show the Footer
        Ui::show_query_stats();
        Ui::show_footer();
    }
}
