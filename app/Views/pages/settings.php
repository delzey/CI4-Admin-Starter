<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
            <i class="fa fa-bars me-2"></i>
            <h5 class="mb-0">Application and Site Settings</h5>
        </div>
    <div class="card-body">
        <?= csrf_field() ?>

        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general" type="button" role="tab">
                    <i class="fa fa-sliders-h"></i> General
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-company" data-bs-toggle="tab" data-bs-target="#pane-company" type="button" role="tab">
                    <i class="fa fa-building"></i> Company / Auth
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-server" data-bs-toggle="tab" data-bs-target="#pane-server" type="button" role="tab">
                    <i class="fa fa-server"></i> Server Info
                </button>
            </li>
        </ul>
        <div class="tab-content pt-3">
            <!-- GENERAL TAB -->
            <div class="tab-pane fade show active" id="pane-general" role="tabpanel">
                <form id="formGeneral" autocomplete="off">

                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Site Name <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm" name="siteName" value="<?= esc(setting('Site.siteName')) ?>" required>
                                    <div class="form-text">Appears in admin and throughout the site.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-control form-control-sm select2" name="appTimezone" id="appTimezone" required>
                                        <?php foreach ($tzList as $tz): ?>
                                        <option value="<?= esc($tz) ?>" <?= $tz === $currentTZ ? 'selected' : '' ?>><?= esc($tz) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Idle Timeout (minutes)</label>
                                    <input type="number" min="1" max="240" class="form-control form-control-sm" name="idleTimeoutMinutes" value="<?= esc(setting('Site.idleTimeoutMinutes') ?? 6) ?>">
                                    <div class="form-text">Auto logout after inactivity.</div>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Site Description</label>
                                    <textarea rows="2" class="form-control" name="siteDescription"><?= esc(setting('Site.siteDescription')) ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Keywords</label>
                                    <textarea rows="2" class="form-control" name="siteKeyWords"><?= esc(setting('Site.siteKeyWords')) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="siteOnline" name="siteOnline" value="1" <?= (setting('Site.siteOnline') ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="siteOnline">Site Online</label>
                                    </div>
                                    <div class="form-text">If off, only permitted users can access the site.</div>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label">Date Format</label>
                                    <?php
                                      $dateChoices = ['m/d/Y','d/m/Y','d-m-Y','Y-m-d','M j, Y'];
                                      $curDate = (string) $dateFormat;
                                    ?>
                                    <?php foreach ($dateChoices as $i => $fmt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dateFormat" id="dateFmt<?= $i ?>" value="<?= esc($fmt) ?>" <?= $curDate === $fmt ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="dateFmt<?= $i ?>">
                                            <?= esc(date($fmt)) ?> <span class="text-muted"> (<?= esc($fmt) ?>)</span>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Time Format</label>
                                    <?php
                                      $timeChoices = ['g:i A' => '12 hour w/ AM/PM', 'H:i' => '24 hour'];
                                      $curTime = (string) $timeFormat;
                                      $j=0;
                                    ?>
                                    <?php foreach ($timeChoices as $fmt => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="timeFormat" id="timeFmt<?= $j ?>" value="<?= esc($fmt) ?>" <?= $curTime === $fmt ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="timeFmt<?= $j ?>">
                                            <?= esc(date($fmt)) ?> <span class="text-muted"> <?= esc($label) ?></span>
                                        </label>
                                    </div>
                                    <?php $j++; endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end pt-3">
                        <button type="button" id="btnSaveGeneral" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-save"></i> Update General
                        </button>
                    </div>
                </form>
            </div>

            <!-- COMPANY TAB -->
            <div class="tab-pane fade" id="pane-company" role="tabpanel">
                <form id="formCompany" autocomplete="off">

                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allowRegistration" value="1" id="allowRegistration" <?= setting('Auth.allowRegistration') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allowRegistration">Allow Registration</label>
                                    </div>
                                    <div class="form-text">If enabled, users may create accounts.</div>

                                    <hr>

                                    <label class="form-label">Minimum Password Length</label>
                                    <input class="form-control form-control-sm" name="minimumPasswordLength" value="<?= esc(setting('Auth.minimumPasswordLength') ?? 8) ?>">
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="emailActivation" value="1" id="emailActivation" <?= !empty(setting('Auth.actions')['register']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="emailActivation">Email Activation</label>
                                    </div>
                                    <div class="form-text">Require email activation on registration.</div>

                                    <hr>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="email2FA" value="1" id="email2FA" <?= (setting('Auth.actions')['login'] ?? null) === 'CodeIgniter\\Shield\\Authentication\\Actions\\Email2FA' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email2FA">Email 2FA</label>
                                    </div>
                                    <div class="form-text">Require email 2FA on login.</div>

                                    <hr>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allowGoogleLogins" value="1" id="allowGoogleLogins" <?= setting('Auth.allowGoogleLogins') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allowGoogleLogins">Allow Google Logins</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Default Group</label>
                                    <?php $i=0; foreach ($groups as $group => $info): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="defaultGroup<?= $i ?>" name="defaultGroup" value="<?= esc($group) ?>" <?= $group === $defaultGroup ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="defaultGroup<?= $i ?>"><?= esc($info['title'] ?? $group) ?></label>
                                    </div>
                                    <?php $i++; endforeach; ?>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Remember Me Length</label>
                                    <select class="form-select form-select-sm" name="rememberLength">
                                        <?php foreach ($rememberOptions as $title => $seconds): ?>
                                        <option value="<?= $seconds ?>" <?= (string)($seconds) === (string)(setting('Auth.sessionConfig')['rememberLength'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc($title) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Password Validators</label>

                                    <?php
                                      $curValidators = (array) (setting('Auth.passwordValidators') ?? []);
                                      $valOptions = [
                                        'CodeIgniter\\Shield\\Authentication\\Passwords\\CompositionValidator'     => 'Composition Validator',
                                        'CodeIgniter\\Shield\\Authentication\\Passwords\\NothingPersonalValidator' => 'Nothing Personal Validator',
                                        'CodeIgniter\\Shield\\Authentication\\Passwords\\DictionaryValidator'      => 'Dictionary Validator',
                                        'CodeIgniter\\Shield\\Authentication\\Passwords\\PwnedValidator'           => 'Pwned Validator',
                                      ];
                                    ?>

                                    <?php foreach ($valOptions as $val => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="validators[]" value="<?= esc($val) ?>" <?= in_array($val, $curValidators, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= esc($label) ?></label>
                                    </div>
                                    <?php endforeach; ?>

                                    <div class="form-text mt-2">
                                        Unchecking reduces security requirements. Prefer only one of Dictionary/Pwned.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <ul class="text-muted small mb-0">
                                        <li>Composition checks length rules.</li>
                                        <li>Nothing Personal checks similarity to username/email.</li>
                                        <li>Dictionary checks common leaked passwords.</li>
                                        <li>Pwned checks leaked passwords via third party.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end pt-3">
                        <button type="button" id="btnSaveCompany" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-save"></i> Update Company/Auth
                        </button>
                    </div>

                </form>
            </div>

            <div class="tab-pane fade" id="pane-server" role="tabpanel">

                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row">
                            <!-- Left table -->
                            <div class="col-md-6">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Server Date/Time</td>
                                            <td><?= esc(date(($dateFormat ?? 'M j, Y') . ' ' . ($timeFormat ?? 'g:i A'))) ?></td>
                                        </tr>
                                        <tr>
                                            <td>PHP Version</td>
                                            <td><?= esc(PHP_VERSION) ?></td>
                                        </tr>
                                        <tr>
                                            <td>CodeIgniter Version</td>
                                            <td><?= esc($ciVersion ?? 'Unknown') ?></td>
                                        </tr>
                                        <tr>
                                            <td>SQL Engine</td>
                                            <td><?= esc(($dbDriver ?? 'Unknown') . ' ' . ($dbVersion ?? 'Unknown')) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Server OS</td>
                                            <td><?= esc(php_uname('s') . ' ' . php_uname('r') . ' (' . php_uname('m') . ')') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Server Load</td>
                                            <td><?= $serverLoad !== null ? esc(number_format((float)$serverLoad, 1)) : 'Unknown' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Max Upload</td>
                                            <td><?= esc(ini_get('upload_max_filesize')) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Max POST</td>
                                            <td><?= esc(ini_get('post_max_size')) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Memory Limit</td>
                                            <td><?= esc(ini_get('memory_limit')) ?></td>
                                        </tr>
                                        <tr>
                                            <td>PHP</td>
                                            <td>
                                                <?php if (auth()->user() && auth()->user()->inGroup('superadmin')): ?>
                                                <button type="button" id="btnPhpInfo" class="btn btn-sm btn-primary" data-href="<?= site_url('settings/php-info') ?>">
                                                    View PHP Info
                                                </button>
                                                <?php else: ?>
                                                <span class="text-muted">Not authorized</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Right table -->
                            <div class="col-md-6">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <td colspan="3"><i class="far fa-file"></i> .env</td>
                                            <td>
                                                <?php if (is_file(ROOTPATH . '.env')): ?>
                                                <i class="fa fa-check text-success"></i>&nbsp; <span class="text-success">present</span>
                                                <?php else: ?>
                                                <i class="fa fa-times text-danger"></i>&nbsp; <span class="text-danger">missing</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <?php if (!empty($envFile)): ?>
                                        <tr>
                                            <td>Permission</td>
                                            <td colspan="2"><?= esc($envFile['permission']) ?></td>
                                            <td>Size: <?= esc(number_to_size((int)$envFile['size'])) ?></td>
                                        </tr>
                                        <?php endif; ?>

                                        <tr>
                                            <td colspan="3"><i class="far fa-folder"></i> /writable</td>
                                            <td>
                                                <?php if (is_really_writable(WRITEPATH)): ?>
                                                <i class="fa fa-check text-success"></i>&nbsp; <span class="text-success">writable</span>
                                                <?php else: ?>
                                                <i class="fa fa-times text-danger"></i>&nbsp; <span class="text-danger">not writable</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3">writable/cache</td>
                                            <td><?= is_really_writable(WRITEPATH . 'cache') ? '<span class="text-success">writable</span>' : '<span class="text-danger">not writable</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3">writable/logs</td>
                                            <td><?= is_really_writable(WRITEPATH . 'logs') ? '<span class="text-success">writable</span>' : '<span class="text-danger">not writable</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3">writable/uploads</td>
                                            <td><?= is_really_writable(WRITEPATH . 'uploads') ? '<span class="text-success">writable</span>' : '<span class="text-muted">n/a</span>' ?></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="phpInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary py-2">
        <h5 class="modal-title text-white mb-0">
          <i class="fa fa-code"></i>&nbsp; PHP Info
        </h5>
        <button type="button" class="btn btn-sm text-white" data-bs-dismiss="modal">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="modal-body p-0" style="height: 75vh;">
        <iframe id="phpInfoFrame" src="about:blank" style="border:0;width:100%;height:100%;"></iframe>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.siteData = window.siteData || {};
window.siteData.csrfName = '<?= esc(csrf_token()) ?>';
window.siteData.csrfHash = '<?= esc(csrf_hash()) ?>';
</script>
<script src="<?= asset('app/js/settings_tabs.js') ?>"></script>
<script>
if (window.jQuery && jQuery.fn.select2) {
    jQuery('.select2').select2();
}
</script>

<?= $this->endSection() ?>