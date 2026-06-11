document.addEventListener('alpine:init', function () {
    function model(key, fallback) {
        return {
            get() {
                if (!this.value || !(key in this.value)) {
                    return fallback;
                }

                return this.value[key];
            },

            set(value) {
                const newValue = { ...this.value, [key]: value };
                this.$emit("update:value", newValue);
            },
        };
    }

    Statamic.$components.register('cta-fieldtype', {
        template: `
            <ui-button v-if="meta.enabled === 'optional' && !enabled" @click="enabled = true">
                {{ meta.addLabel }}
            </ui-button>

            <div v-else class="flex items-top gap-3 flex-1">
                <ui-button v-if="meta.enabled === 'optional'" @click="enabled = false">
                    Remove
                </ui-button>

                <ui-button-group>
                    <ui-button
                        class="px-1"
                        :variant="option === 'url' ? 'pressed' : 'default'"
                        @click="option = 'url'"
                    >
                        Url
                    </ui-button>

                    <ui-button
                        class="px-1"
                        :variant="option === 'entry' ? 'pressed' : 'default'"
                        @click="option = 'entry'"
                    >
                        Entry
                    </ui-button>

                    <ui-button
                        class="px-1"
                        :variant="option === 'asset' ? 'pressed' : 'default'"
                        @click="option = 'asset'"
                    >
                        Asset
                    </ui-button>
                </ui-button-group>

                <div class="flex flex-col flex-1">
                    <div class="pb-1">
                        <text-fieldtype
                            v-if="!option || option === 'url'"
                            v-model="url"
                            :config="{ placeholder: 'https://...' }"
                        />

                        <relationship-fieldtype
                            v-if="option === 'entry'"
                            ref="entries"
                            handle="entry"
                            :style="!entry?.[0] ? 'padding-block: .25rem' : ''"
                            :config="meta.entry.config"
                            :meta="meta.entry.meta"
                            :value="entry"
                            @update:meta="meta.entry.meta = $event"
                            @update:value="entriesSelected"
                        />

                        <div
                            v-if="option === 'asset'"
                            :class="{ 'link-fieldtype': !asset?.[0] }"
                        >
                            <assets-fieldtype
                                ref="assets"
                                handle="asset"
                                :config="meta.asset.config"
                                :meta="meta.asset.meta"
                                :value="asset"
                                @update:value="asset = $event"
                            />
                        </div>
                    </div>

                    <ui-checkbox
                        v-model="openInNewTab"
                        class="mr-1"
                        label="Open in new tab?"
                    />
                </div>

                <div v-if="meta.label === 'show'" class="flex flex-col flex-1">
                    <div class="pb-1"> <text-fieldtype
                            v-model="label"
                            :config="{ placeholder: meta.label_placeholder || 'Label' }"
                        />
                    </div>

                    <ui-checkbox
                        v-model="customAriaLabel"
                        class="mr-1"
                        label="Add Custom Aria Label?"
                    />
                </div>

                <text-fieldtype
                    class="flex-1"
                    v-if="customAriaLabel"
                    v-model="ariaLabel"
                    :config="{ placeholder: meta.aria_label_placeholder || 'Aria Label' }"
                />
            </div>
        `,
        name: "CtaFieldtype",
        props: {
            value: Object,
            meta: Object,
        },
        computed: {
            enabled: model("enabled"),
            option: model("option", 'url'),
            url: model("url"),
            entry: model("entry", []),
            asset: model("asset", []),
            label: model("label"),
            ariaLabel: model("ariaLabel"),
            openInNewTab: model("open_in_new_tab", false),
            customAriaLabel: model("custom_aria_label", false),

            replicatorPreview() {
                switch (this.option) {
                    case "entry":
                        return this.meta?.entry?.meta?.data?.[0]?.title;
                    case "asset":
                        return this.meta?.asset?.meta?.data?.[0]?.basename;
                    case "url":
                        return this.url ? `Url: "${this.url}"` : null;
                }
                return "<em>No CTA.</em>";
            },
        },
        methods: {
            entriesSelected(entries) {
                this.value.entry = entries;

                const newValue = { ...this.value, entry: entries };
                this.$emit("update:value", newValue);

                const newMeta = { ...this.meta, initialSelectedEntries: entries };
                this.$emit("update:meta", newMeta);
            },
        },
    });
});
