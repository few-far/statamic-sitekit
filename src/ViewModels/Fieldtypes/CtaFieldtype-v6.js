import _ from "lodash";

function model(key, fallback) {
    return {
        get() {
            if (!_.has(this.value, key)) {
                return fallback;
            }

            return _.get(this.value, key);
        },

        set(value) {
            const newValue = _.assign({}, this.value, { [key]: value });
            this.$emit("update:value", newValue);
        },
    };
}

export default {
    template: `
		<div>
			<ui-button v-if="meta.enabled === 'optional' && !enabled" class="btn" @click="enabled = true">
				{{ meta.addLabel }}
			</ui-button>

			<div v-else class="flex items-top gap-3">
				<ui-button v-if="meta.enabled ==='optional'" @click="enabled = false" class="text-sm text-blue hover:text-black">
					Remove
				</ui-button>

				<div>
					<ui-button-group>
						<ui-button class="btn px-1" :variant="!option || option === 'url' ? 'pressed' : ''"  @click="option = 'url'">
							Url
						</ui-button>

						<ui-button class="btn px-1" :variant="option === 'entry' ? 'pressed' : ''"  @click="option = 'entry'">
							Entry
						</ui-button>

						<ui-button class="btn px-1" :variant="option === 'asset' ? 'pressed' : ''"  @click="option = 'asset'">
							Asset
						</ui-button>
					</ui-button-group>
				</div>

				<div class="flex-1 max-w-[45%]">
					<div class="pb-1">
						<!-- Text field -->
						<text-fieldtype
							v-if="!option || option === 'url'"
							v-model="url"
							:config="{ placeholder: 'https://...' }"
						/>

						<!-- Entry select -->
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

						<!-- Asset select -->
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

				<div v-if="meta.label === 'show'" class="flex-1">
					<!-- Text field -->
					<text-fieldtype
						v-model="label"
						:config="{ placeholder: meta.label_placeholder || 'Label' }"
					/>
				</div>
			</div>
		</div>
	`,

    name: "CtaFieldtype",
    // mixins: [Fieldtype], // If Fieldtype is a mixin, import and add here
    props: {
        value: Object,
        meta: Object,
    },
    computed: {
        enabled: model("enabled"),
        option: model("option"),
        url: model("url"),
        entry: model("entry", []),
        asset: model("asset", []),
        label: model("label"),
        openInNewTab: model("open_in_new_tab", false),

        replicatorPreview() {
            switch (this.option) {
                case "entry":
                    return _.get(this.meta, "entry.meta.data.0.title");
                case "asset":
                    return _.get(this.meta, "asset.meta.data.0.basename");
                case "url":
                    return this.url ? `Url: "${this.url}"` : null;
            }
            return "<em>No CTA.</em>";
        },
    },
    methods: {
        entriesSelected(entries) {
            this.value.entry = entries;
            const newValue = _.assign({}, this.value, { entry: entries });
            this.$emit("update:value", newValue);
            const newMeta = _.assign({}, this.meta, {
                initialSelectedEntries: entries,
            });
            this.$emit("update:meta", newMeta);
        },
    },
};
