/**
 * ScandiPWA - Progressive Web App for Magento
 *
 * Copyright © Scandiweb, Inc. All rights reserved.
 * See LICENSE for license details.
 *
 * @license OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * @package scandipwa/base-theme
 * @link https://github.com/scandipwa/base-theme
 */

import { PureComponent } from 'react';
import PropTypes from 'prop-types';
import Field from 'Component/Field';
import './StoreSwitcher.style';

class StoreSwitcher extends PureComponent {
    static propTypes = {
        storeList: PropTypes.arrayOf(
            PropTypes.objectOf(
                PropTypes.string
            )
        ).isRequired,
        currentStoreCode: PropTypes.string.isRequired,
        handleStoreSelect: PropTypes.func.isRequired
    };

    render() {
        const {
            storeList,
            handleStoreSelect,
            currentStoreCode
        } = this.props;

        const activeStoreData = storeList.filter(data => data.value === currentStoreCode);
        var WebsiteList = storeList.filter(data => data.website_id === activeStoreData[0].website_id);
        if (storeList.length <= 1) return null;

        return (
            <div block="StoreSwitcher">
                <Field
                  id="StoreSwitcher"
                  name="StoreSwitcher"
                  type="select"
                  placeholder={ __('Select store') }
                  selectOptions={ WebsiteList }
                  value={ currentStoreCode }
                  onChange={ handleStoreSelect }
                />
            </div>
        );
    }
}

export default StoreSwitcher;
