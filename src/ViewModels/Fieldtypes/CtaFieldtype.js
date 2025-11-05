document.addEventListener('alpine:init', function () {
	function model(key, fallback) {
		return {
			get() {
				if (! _.has(this.value, key)) {
					return fallback;
				}

				return _.get(this.value, key);
			},

			set(value) {
				this.update(_.assign({}, this.value, { [key]: value }));
			},
		};
	}

	Statamic.$components.register('cta-fieldtype', {
		template: `
			<div>
				<button v-if="meta.enabled === 'optional' && !enabled" class="btn" @click="enabled = true">
					{{ meta.addLabel }}
				</button>

				<div v-else class="flex items-top gap-3">
					<div>
						<div class="btn-group">
							<button class="btn px-1" :class="{ 'active': !option || option === 'url' }"  @click="option = 'url'">
								Url
							</button>

							<button class="btn px-1" :class="{ 'active': option === 'entry' }"  @click="option = 'entry'">
								Entry
							</button>

							<button class="btn px-1" :class="{ 'active': option === 'asset' }"  @click="option = 'asset'">
								Asset
							</button>
						</div>

						<button v-if="meta.enabled ==='optional'" @click="enabled = false" class="text-xs mt-1 text-blue hover:text-black">
							Remove
						</button>
					</div>

					<div class="flex-1">
						<div>
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
								v-model="entry"
								:config="meta.entry.config"
								:meta="meta.entry.meta"
								:class="{ 'py-2': !entry?.[0] }"
								@meta-updated="meta.entry.meta = $event"
							/>

							<!-- Entry select -->
							<div
								v-if="option === 'asset'"
								:class="{ 'assets-fieldtype': asset?.[0] }"
							>
								<assets-fieldtype
									ref="assets"
									handle="asset"
									v-model="asset"
									:config="meta.asset.config"
									:meta="meta.asset.meta"
									@meta-updated="meta.asset.meta = $event"
								/>
							</div>
						</div>

						<label class="pointer-cursor text-xs mt-1 font-normal">
							<input type="checkbox" v-model="openInNewTab" class="mr-1" v>
							Open in new tab?
						</label>
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

		mixins: [Fieldtype],

		computed: {
			enabled: model('enabled'),
			option: model('option'),
			url: model('url'),
			entry: model('entry', []),
			asset: model('asset', []),
			label: model('label'),
			openInNewTab: model('open_in_new_tab', false),

			replicatorPreview() {
				return (() => {
					switch (this.option) {
						case 'entry': return _.get(this.meta, 'entry.meta.data.0.title');
						case 'asset': return _.get(this.meta, 'asset.meta.data.0.title');
						case 'url': return this.url ? `Url: "${ this.url }"` : null;
					}
				})() || '<em>No CTA.</em>';
			},
		},
	});
});
