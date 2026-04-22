<?php
$pageTitle = 'Personalize Your Workspace';
require 'includes/header.php';

if (!empty($user['is_admin'])) {
    header('Location: dashboard');
    exit;
}

$draftData = $_SESSION['onboarding-data'] ?? [];
$onboardingError = $_SESSION['onboarding-error'] ?? null;

unset($_SESSION['onboarding-data'], $_SESSION['onboarding-error']);

$selectedAccountType = normalizeOnboardingAccountType($draftData['account_type'] ?? $onboardingState['account_type'] ?? null);
$selectedRole = normalizeOnboardingRole($draftData['profile_role'] ?? $onboardingState['profile_role'] ?? null);
$selectedStage = normalizeOnboardingStage($draftData['engagement_stage'] ?? $onboardingState['engagement_stage'] ?? null);
$selectedCategories = parsePreferredCategoryIds($draftData['preferred_categories'] ?? $onboardingState['preferred_category_ids'] ?? []);

$categoryOptions = [];
$categoryResult = mysqli_query($connection, "SELECT id, title, description FROM categories ORDER BY title ASC");
if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoryOptions[] = $row;
    }
}

$requiresAccountType = $selectedAccountType === null;
$totalSteps = $requiresAccountType ? 4 : 3;
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: ($user['email'] ?? 'there');

$stageHints = [
    'exploring' => 'We will keep the experience light and discovery-focused.',
    'ready' => 'We will highlight stronger matches and nudge you toward conversations.',
    'urgent' => 'We will surface high-intent ideas and stronger connection prompts first.',
];
?>
<body class="onboarding-body">
    <div class="onboarding-shell">
        <div class="onboarding-glow onboarding-glow--one"></div>
        <div class="onboarding-glow onboarding-glow--two"></div>

        <div class="onboarding-frame">
            <div class="onboarding-aside">
                <span class="onboarding-kicker">New workspace</span>
                <h1>Help us personalize your experience.</h1>
                <p>We will use your answers to shape your dashboard, idea feed, and the prompts you see next.</p>

                
            </div>

            <div class="onboarding-panel">
                <div class="onboarding-progress">
                    <div class="onboarding-progress__top">
                        <span class="onboarding-progress__label" id="onboarding-step-label">Step 1 of <?= $totalSteps ?></span>
                        <span class="onboarding-progress__hint">Takes less than a minute</span>
                    </div>
                    <div class="onboarding-progress__bar">
                        <span id="onboarding-progress-bar"></span>
                    </div>
                </div>

                <?php if ($onboardingError): ?>
                    <div class="alert alert-danger rounded-4 border-0 mb-4">
                        <?= htmlspecialchars($onboardingError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="onboarding-logic" method="POST" id="onboarding-form" novalidate>
                    <?php if (!$requiresAccountType && $selectedAccountType !== null): ?>
                        <input type="hidden" name="account_type" value="<?= htmlspecialchars($selectedAccountType, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>

                    <?php if ($requiresAccountType): ?>
                        <section class="onboarding-step" data-validate="account_type">
                            <div class="onboarding-step__copy">
                                <span class="onboarding-step__eyebrow">Setup focus</span>
                                <h2>What kind of experience should we optimize for?</h2>
                                <p>This decides the workspace we lean into first.</p>
                            </div>

                            <div class="onboarding-choice-grid">
                                <label class="onboarding-choice-card">
                                    <input type="radio" name="account_type" value="poster" <?= $selectedAccountType === 'poster' ? 'checked' : '' ?>>
                                    <span class="onboarding-choice-card__body">
                                        <span class="onboarding-choice-card__title">Poster</span>
                                        <span class="onboarding-choice-card__text">You want to publish ideas and attract the right builders or backers.</span>
                                    </span>
                                </label>
                                <label class="onboarding-choice-card">
                                    <input type="radio" name="account_type" value="seeker" <?= $selectedAccountType === 'seeker' ? 'checked' : '' ?>>
                                    <span class="onboarding-choice-card__body">
                                        <span class="onboarding-choice-card__title">Seeker</span>
                                        <span class="onboarding-choice-card__text">You want to browse ideas to build, join, or invest in.</span>
                                    </span>
                                </label>
                                <label class="onboarding-choice-card">
                                    <input type="radio" name="account_type" value="both" <?= $selectedAccountType === 'both' ? 'checked' : '' ?>>
                                    <span class="onboarding-choice-card__body">
                                        <span class="onboarding-choice-card__title">Both</span>
                                        <span class="onboarding-choice-card__text">You want to post your own ideas and discover strong opportunities too.</span>
                                    </span>
                                </label>
                            </div>
                        </section>
                    <?php endif; ?>

                    <section class="onboarding-step" data-validate="profile_role">
                        <div class="onboarding-step__copy">
                            <h2>Which role best describes you today?</h2>
                            <p>This tag is shown when people browse your profile and posts.</p>
                        </div>

                        <div class="onboarding-choice-grid onboarding-choice-grid--compact">
                            <?php foreach (['founder' => 'Founder', 'developer' => 'Developer', 'investor' => 'Investor', 'creative' => 'Creative'] as $value => $label): ?>
                                <label class="onboarding-choice-card">
                                    <input type="radio" name="profile_role" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $value ? 'checked' : '' ?>>
                                    <span class="onboarding-choice-card__body">
                                        <span class="onboarding-choice-card__title"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="onboarding-step" data-validate="preferred_categories">
                        <div class="onboarding-step__copy">
                            <span class="onboarding-step__eyebrow">Feed filters</span>
                            <h2>Which categories should we prioritize first?</h2>
                            <p>Pick up to 4 so your idea feed feels relevant from day one.</p>
                        </div>

                        <?php if (empty($categoryOptions)): ?>
                            <div class="alert alert-warning rounded-4 border-0">
                                Categories have not been set up yet, so we cannot personalize the feed just yet.
                            </div>
                        <?php else: ?>
                            <div class="onboarding-category-grid" id="onboarding-category-grid">
                                <?php foreach ($categoryOptions as $category): ?>
                                    <?php
                                    $categoryId = (int) ($category['id'] ?? 0);
                                    $isSelected = in_array($categoryId, $selectedCategories, true);
                                    ?>
                                    <label class="onboarding-category-card">
                                        <input
                                            type="checkbox"
                                            name="preferred_categories[]"
                                            value="<?= $categoryId ?>"
                                            <?= $isSelected ? 'checked' : '' ?>>
                                        <span class="onboarding-category-card__body">
                                            <span class="onboarding-category-card__title"><?= htmlspecialchars($category['title'] ?? 'Category', ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="onboarding-category-card__text">
                                            </span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="onboarding-helper" id="category-helper">Choose between 1 and 4 categories.</div>
                        <?php endif; ?>
                    </section>

                    <section class="onboarding-step" data-validate="engagement_stage">
                        <div class="onboarding-step__copy">
                            <span class="onboarding-step__eyebrow">Connection mode</span>
                            <h2>How fast are you trying to move right now?</h2>
                            <p>We use this to decide how strongly we push connection prompts and next steps.</p>
                        </div>

                        <div class="onboarding-choice-grid onboarding-choice-grid--compact">
                            <?php foreach (['exploring' => 'Exploring', 'ready' => 'Ready', 'urgent' => 'Urgent'] as $value => $label): ?>
                                <label class="onboarding-choice-card">
                                    <input type="radio" name="engagement_stage" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedStage === $value ? 'checked' : '' ?>>
                                    <span class="onboarding-choice-card__body">
                                        <span class="onboarding-choice-card__title"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="onboarding-choice-card__text"><?= htmlspecialchars($stageHints[$value], ENT_QUOTES, 'UTF-8') ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <div class="onboarding-error" id="onboarding-inline-error" hidden></div>

                    <div class="onboarding-actions">
                        <button type="button" class="btn btn-light rounded-pill px-4" id="onboarding-back-btn">Back</button>
                        <div class="d-flex gap-2">
                            <a href="logout" class="btn btn-outline-secondary rounded-pill px-4">Exit</a>
                            <button type="button" class="btn btn-primary rounded-pill px-4" id="onboarding-next-btn">Continue</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="onboarding-submit-btn">Finish setup</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        #page-topbar {
            display: none !important;
        }

        .onboarding-body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(38, 99, 255, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255, 132, 59, 0.14), transparent 28%),
                linear-gradient(135deg, #f6f8fc 0%, #ffffff 100%);
        }

        .onboarding-shell {
            position: relative;
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .onboarding-glow {
            position: absolute;
            border-radius: 999px;
            filter: blur(22px);
            opacity: .65;
            pointer-events: none;
        }

        .onboarding-glow--one {
            width: 18rem;
            height: 18rem;
            background: rgba(38, 99, 255, 0.18);
            top: 8%;
            left: 6%;
        }

        .onboarding-glow--two {
            width: 15rem;
            height: 15rem;
            background: rgba(255, 132, 59, 0.18);
            bottom: 10%;
            right: 7%;
        }

        .onboarding-frame {
            position: relative;
            width: min(1160px, 100%);
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(10px);
        }

        .onboarding-aside {
            padding: 2.4rem;
            color: #f8fbff;
            background:
                linear-gradient(180deg, rgba(13, 36, 89, 0.96) 0%, rgba(17, 24, 39, 0.95) 100%);
        }

        .onboarding-kicker {
            display: inline-flex;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .onboarding-aside h1 {
            font-size: clamp(2rem, 3vw, 2.6rem);
            line-height: 1.05;
            margin-bottom: 1rem;
            color: inherit;
        }

        .onboarding-aside p {
            color: rgba(248, 251, 255, 0.74);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .onboarding-aside__meta {
            display: grid;
            gap: 1rem;
        }

        .onboarding-meta-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 1rem 1.05rem;
            background: rgba(255, 255, 255, 0.05);
        }

        .onboarding-meta-label {
            display: block;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(248, 251, 255, 0.55);
            margin-bottom: .4rem;
        }

        .onboarding-panel {
            padding: 2.2rem;
        }

        .onboarding-progress {
            margin-bottom: 2rem;
        }

        .onboarding-progress__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .7rem;
        }

        .onboarding-progress__label {
            font-weight: 700;
            color: #0f172a;
        }

        .onboarding-progress__hint {
            color: #64748b;
            font-size: .92rem;
        }

        .onboarding-progress__bar {
            height: .65rem;
            border-radius: 999px;
            background: #e9eef8;
            overflow: hidden;
        }

        .onboarding-progress__bar span {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
            transition: width .25s ease;
        }

        .onboarding-step__copy {
            margin-bottom: 1.5rem;
        }

        .onboarding-step__eyebrow {
            display: inline-block;
            margin-bottom: .75rem;
            color: #2563eb;
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .onboarding-step h2 {
            font-size: clamp(1.7rem, 2vw, 2.15rem);
            line-height: 1.15;
            color: #0f172a;
            margin-bottom: .6rem;
        }

        .onboarding-step p {
            color: #64748b;
            line-height: 1.65;
            margin-bottom: 0;
        }

        .onboarding-choice-grid,
        .onboarding-category-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .onboarding-choice-grid--compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .onboarding-choice-card,
        .onboarding-category-card {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .onboarding-choice-card input,
        .onboarding-category-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .onboarding-choice-card__body,
        .onboarding-category-card__body {
            display: block;
            height: 100%;
            padding: 1.15rem 1.2rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.2rem;
            background: #fff;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .onboarding-choice-card__title,
        .onboarding-category-card__title {
            display: block;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .35rem;
        }

        .onboarding-choice-card__text,
        .onboarding-category-card__text {
            display: block;
            color: #64748b;
            line-height: 1.55;
            font-size: .94rem;
        }

        .onboarding-choice-card input:checked + .onboarding-choice-card__body,
        .onboarding-category-card input:checked + .onboarding-category-card__body {
            border-color: rgba(37, 99, 235, 0.65);
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.95) 0%, #ffffff 100%);
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.12);
            transform: translateY(-2px);
        }

        .onboarding-category-card input:disabled + .onboarding-category-card__body {
            opacity: .5;
            cursor: not-allowed;
        }

        .onboarding-helper,
        .onboarding-error {
            margin-top: 1rem;
            color: #475569;
            font-size: .95rem;
        }

        .onboarding-error {
            color: #b42318;
            background: rgba(254, 242, 242, 0.96);
            border: 1px solid rgba(220, 38, 38, 0.1);
            border-radius: 1rem;
            padding: .9rem 1rem;
        }

        .onboarding-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 991.98px) {
            .onboarding-shell {
                padding: 1rem;
            }

            .onboarding-frame {
                grid-template-columns: 1fr;
            }

            .onboarding-choice-grid,
            .onboarding-choice-grid--compact,
            .onboarding-category-grid {
                grid-template-columns: 1fr;
            }

            .onboarding-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .onboarding-actions > div {
                display: grid !important;
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="account/assets/libs/jquery/jquery.min.js"></script>
    <script src="account/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="account/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="account/assets/libs/eva-icons/eva.min.js"></script>
    <script src="account/assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('onboarding-form');
            const steps = Array.from(document.querySelectorAll('.onboarding-step'));
            const progressBar = document.getElementById('onboarding-progress-bar');
            const stepLabel = document.getElementById('onboarding-step-label');
            const nextButton = document.getElementById('onboarding-next-btn');
            const backButton = document.getElementById('onboarding-back-btn');
            const submitButton = document.getElementById('onboarding-submit-btn');
            const errorBox = document.getElementById('onboarding-inline-error');
            const categoryInputs = Array.from(document.querySelectorAll('input[name="preferred_categories[]"]'));
            const categoryHelper = document.getElementById('category-helper');
            let currentStep = 0;

            function setError(message) {
                if (!message) {
                    errorBox.hidden = true;
                    errorBox.textContent = '';
                    return;
                }

                errorBox.hidden = false;
                errorBox.textContent = message;
            }

            function refreshCategoryLimit() {
                if (!categoryInputs.length) {
                    return;
                }

                const checkedCount = categoryInputs.filter(input => input.checked).length;
                categoryInputs.forEach(input => {
                    input.disabled = !input.checked && checkedCount >= 4;
                });

                if (categoryHelper) {
                    categoryHelper.textContent = checkedCount > 0
                        ? checkedCount + ' of 4 categories selected'
                        : 'Choose between 1 and 4 categories.';
                }
            }

            function validateStep(step) {
                switch (step.dataset.validate) {
                    case 'account_type':
                        return form.querySelector('input[name="account_type"]:checked')
                            ? ''
                            : 'Choose whether you want a poster, seeker, or hybrid experience.';
                    case 'profile_role':
                        return form.querySelector('input[name="profile_role"]:checked')
                            ? ''
                            : 'Choose the role you want shown on your profile.';
                    case 'preferred_categories': {
                        const checkedCount = form.querySelectorAll('input[name="preferred_categories[]"]:checked').length;
                        if (checkedCount === 0) {
                            return 'Pick at least one category so we can personalize your feed.';
                        }
                        if (checkedCount > 4) {
                            return 'Choose up to 4 categories.';
                        }
                        return '';
                    }
                    case 'engagement_stage':
                        return form.querySelector('input[name="engagement_stage"]:checked')
                            ? ''
                            : 'Choose the pace that matches how fast you want to move.';
                    default:
                        return '';
                }
            }

            function renderStep() {
                steps.forEach((step, index) => {
                    step.hidden = index !== currentStep;
                });

                const progressValue = ((currentStep + 1) / steps.length) * 100;
                progressBar.style.width = progressValue + '%';
                stepLabel.textContent = 'Step ' + (currentStep + 1) + ' of ' + steps.length;
                backButton.style.visibility = currentStep === 0 ? 'hidden' : 'visible';
                nextButton.hidden = currentStep === steps.length - 1;
                submitButton.hidden = currentStep !== steps.length - 1;
                setError('');
                refreshCategoryLimit();
            }

            nextButton.addEventListener('click', function () {
                const validationMessage = validateStep(steps[currentStep]);
                if (validationMessage) {
                    setError(validationMessage);
                    return;
                }

                currentStep += 1;
                renderStep();
            });

            backButton.addEventListener('click', function () {
                if (currentStep === 0) {
                    return;
                }

                currentStep -= 1;
                renderStep();
            });

            form.addEventListener('submit', function (event) {
                const validationMessage = validateStep(steps[currentStep]);
                if (validationMessage) {
                    event.preventDefault();
                    setError(validationMessage);
                }
            });

            form.querySelectorAll('input').forEach(function (input) {
                input.addEventListener('change', function () {
                    setError('');
                    refreshCategoryLimit();
                });
            });

            renderStep();
        });
    </script>
</body>
</html>
