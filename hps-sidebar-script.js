const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { CheckboxControl } = wp.components;
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

const CustomSidebarPanel = ({ hideSiteSearch, hideSearchEngines, setMetaField }) => (
    <PluginDocumentSettingPanel
        name="custom-sidebar-panel"
        title="Visibility Options"
        className="custom-sidebar-panel"
    >
        <CheckboxControl
            label="Hide from site search"
            checked={hideSiteSearch}
            onChange={(value) => setMetaField('hide_from_site_search', value)}
        />
        <CheckboxControl
            label="Hide from search engines"
            checked={hideSearchEngines}
            onChange={(value) => setMetaField('hide_from_search_engines', value)}
        />
    </PluginDocumentSettingPanel>
);

const mapStateToProps = (select) => ({
    hideSiteSearch: select('core/editor').getEditedPostAttribute('meta')['hide_from_site_search'],
    hideSearchEngines: select('core/editor').getEditedPostAttribute('meta')['hide_from_search_engines'],
});

const mapDispatchToProps = (dispatch) => ({
    setMetaField: (fieldName, value) => {
        dispatch('core/editor').editPost({ meta: { [fieldName]: value } });
    },
});

registerPlugin('custom-sidebar-metabox', {
    render: compose([
        withSelect(mapStateToProps),
        withDispatch(mapDispatchToProps)
    ])(CustomSidebarPanel),
    icon: 'visibility',
});