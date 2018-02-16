<?php

/**
 * Adds Alps_RSS_Widget widget.
 */
class Alps_RSS_Widget extends WP_Widget {
 
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'alps_rss_widget', // Base ID
            'ALPS RSS Widget', // Name
            array( 'description' => __( 'An RSS Widget that outputs ALPS code', 'text_domain' ), ) // Args
        );
    }
 
    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );

        if ( empty( $instance['url'] ) ) {
            return null;
        }

        $url = ! empty( $instance['url'] ) ? $instance['url'] : '';
        while ( stristr($url, 'http') != $url )
            $url = substr($url, 1);

        $rss = fetch_feed($url);

        if ( ! is_wp_error($rss) ) {
            $desc = esc_attr(strip_tags(@html_entity_decode($rss->get_description(), ENT_QUOTES, get_option('blog_charset'))));
            if ( empty($title) )
                $title = strip_tags( $rss->get_title() );
            $link = strip_tags( $rss->get_permalink() );
            while ( stristr($link, 'http') != $link )
                $link = substr($link, 1);
        }

        echo '<div class="spacing">';
    
        $title = apply_filters( 'widget_title', $instance['title'] );
 
        echo $before_widget;
    
        if ( ! empty( $title ) ) {
            echo $before_title;
            echo '<h3 class="font--tertiary--m theme--secondary-text-color">' . $title . '</h3>';
            echo $after_title;
        }

        if (is_wp_error($rss)) {
            echo '<div class="content__block">' . __( 'An error has occurred, which probably means the feed is down. Try again later.' ) . '</p></div>';
            unset($rss);
            return;
        }

        $num_posts = 3;
        if ( ! empty( $instance['num_posts'] ) ) {
            $num_posts = $instance['num_posts'];
        }

        // wp_widget_rss_output
        $show_summary = (int) $instance['show_summary'];
        $show_author = (int) $instance['show_author'];
        $show_date = (int) $instance['show_date'];

        if ( !$rss->get_item_quantity() ) {
            echo '<div class="content__block">' . __( 'An error has occurred, which probably means the feed is down. Try again later.' ) . '</p></div>';
            $rss->__destruct();
            unset($rss);
            return;
        }

        foreach ( $rss->get_items( 0, $num_posts ) as $item ) {
            $link = $item->get_link();
            while ( stristr( $link, 'http' ) != $link ) {
                $link = substr( $link, 1 );
            }
            $link = esc_url( strip_tags( $link ) );

            $title = esc_html( trim( strip_tags( $item->get_title() ) ) );
            if ( empty( $title ) ) {
                $title = __( 'Untitled' );
            }

            $desc = @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
            $desc = esc_attr( wp_trim_words( $desc, 55, ' &hellip;' ) );

            $summary = '';
            if ( $show_summary ) {
                $summary = $desc;

                // Change existing [...] to [&hellip;].
                if ( '[...]' == substr( $summary, -5 ) ) {
                    $summary = substr( $summary, 0, -5 ) . '&hellip;';
                }

                $summary = esc_html( $summary );
            }


            echo '<div class="content__block">';
                echo '<h3 class="theme--primary-text-color font--secondary--m"><a href="' . esc_url( $link ) . '" target="_blank">' . $title . '</a></h3>';
                if ($show_summary) {
                    echo '<p>' . strip_tags( $summary ) . '</p>';
                }
            echo '</div>';
            echo '<hr>';
        }
        // wp_widget_rss_output



        echo $args['after_widget'];

        if ( ! is_wp_error($rss) )
            $rss->__destruct();
        unset($rss);
    }
 
    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'New title', 'text_domain' );
        $url = isset( $instance[ 'url' ] ) ? $instance[ 'url' ] : null;
        $num_posts = isset( $instance[ 'num_posts' ] ) ? $instance[ 'num_posts' ] : 3;
        $show_summary = isset( $instance['show_summary'] ) ? (int) $instance['show_summary'] : 0;
        $show_author = isset( $instance['show_author'] ) ? (int) $instance['show_author'] : 0;
        $show_date = isset( $instance['show_date'] ) ? (int) $instance['show_date'] : 0;
    
        ?>
        <p>
        <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_name( 'url' ); ?>"><?php _e( 'Site Url (no trailing slash):' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo $url; ?>" />
        </p>
        <p><label for="<?php echo $this->get_field_name( 'num_posts' ); ?>"><?php _e( 'How many items would you like to display?' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>">
        <?php
        for ( $i = 1; $i <= 20; ++$i ) {
            echo "<option value='$i' " . selected( $num_posts, $i, false ) . ">$i</option>";
        }
        ?>
        </select></p>        
        <p>
            <input id="<?php echo $this->get_field_id( 'show_summary' ); ?>" name="<?php echo $this->get_field_name( 'show_summary' ); ?>" type="checkbox" value="1" <?php checked( $show_summary ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_summary' ); ?>"><?php _e( 'Display item content?' ); ?></label>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" type="checkbox" value="1" <?php checked( $show_author ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e( 'Display item author if available?' ); ?></label>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" type="checkbox" value="1" <?php checked( $show_date ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display item date?' ); ?></label>
        </p>

        <?php
    }
 
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['url'] = esc_url_raw( strip_tags( $new_instance['url'] ) );
        $instance['title'] = isset( $new_instance['title'] ) ? trim( strip_tags( $new_instance['title'] ) ) : '';
        $instance['num_posts'] = ( !empty( $new_instance['num_posts'] ) ) ? strip_tags( $new_instance['num_posts'] ) : '';
        $instance['show_summary'] = isset( $new_instance['show_summary'] ) ? (int) $new_instance['show_summary'] : 0;
        $instance['show_author'] = isset( $new_instance['show_author'] ) ? (int) $new_instance['show_author'] :0;
        $instance['show_date'] = isset( $new_instance['show_date'] ) ? (int) $new_instance['show_date'] : 0;

        return $instance;
    }
 
} // class Alps_RSS_Widget